#!/bin/bash
# fix-xampp.sh - Kill XAMPP ports, repair MariaDB if broken, then restart (macOS)

set -u  # error on unset vars (don't use -e; we want to continue past expected non-fatal errors)

XAMPP_DIR=/Applications/XAMPP/xamppfiles
XAMPP="$XAMPP_DIR/xampp"
MYSQL_BIN="$XAMPP_DIR/bin/mysql"
MYSQL_SERVER="$XAMPP_DIR/bin/mysql.server"
MYSQL_INSTALL_DB="$XAMPP_DIR/bin/mysql_install_db"
DATA_DIR="$XAMPP_DIR/var/mysql"
PORTS=(80 443 3306)

# --- 1. Kill anything holding XAMPP ports --------------------------------
echo "🔪 Killing processes on XAMPP ports..."
for PORT in "${PORTS[@]}"; do
    PIDS=$(sudo lsof -ti tcp:"$PORT")
    if [ -n "$PIDS" ]; then
        echo "  Port $PORT held by: $PIDS — killing..."
        sudo kill -9 $PIDS
    else
        echo "  Port $PORT is free."
    fi
done

sleep 2

# --- 2. Stop XAMPP cleanly in case it's half-running ---------------------
echo "🛑 Stopping XAMPP..."
sudo "$XAMPP" stop

sleep 2

# --- 3. Repair MariaDB system tables if they're missing ------------------
# The mysql/ system db holds privilege tables (db, user, servers, plugin).
# If db.frm is gone, MariaDB can't start. Rebuild without touching user dbs.
if [ ! -f "$DATA_DIR/mysql/db.frm" ] && [ ! -f "$DATA_DIR/mysql/db.MYD" ]; then
    echo "⚠️  MariaDB system tables missing — rebuilding..."

    # Back up project db and InnoDB files before doing anything destructive.
    BACKUP_DIR="$HOME/xampp_backup_$(date +%Y%m%d_%H%M%S)"
    mkdir -p "$BACKUP_DIR"
    if [ -d "$DATA_DIR/myhmsdb" ]; then
        sudo cp -R "$DATA_DIR/myhmsdb" "$BACKUP_DIR/"
        echo "  Backed up myhmsdb to $BACKUP_DIR"
    fi
    sudo cp "$DATA_DIR"/ib* "$BACKUP_DIR/" 2>/dev/null || true

    sudo "$MYSQL_INSTALL_DB" \
        --user=mysql \
        --basedir="$XAMPP_DIR" \
        --datadir="$DATA_DIR"

    sudo chown -R _mysql:_mysql "$DATA_DIR"
    NEEDS_ROOT_FIX=1
else
    echo "✅ MariaDB system tables present."
    NEEDS_ROOT_FIX=0
fi

# --- 4. Clean up stale socket / pid files from any prior kill -9 ---------
sudo rm -f "$DATA_DIR/mysql.sock" /tmp/mysql.sock
sudo rm -f "$DATA_DIR"/*.pid

# --- 5. Start MariaDB on its own first so we can fix root auth -----------
echo "🐬 Starting MariaDB..."
sudo "$MYSQL_SERVER" start
sleep 3

# --- 6. Fix root auth if we just rebuilt the system tables ---------------
# After mysql_install_db, root uses unix_socket auth. phpMyAdmin connects
# over TCP and needs password auth (empty password to match XAMPP defaults
# and the project's mysqli_connect("localhost", "root", "", ...) calls).
if [ "$NEEDS_ROOT_FIX" -eq 1 ]; then
    echo "🔑 Switching root to password auth (empty password)..."
    sudo "$MYSQL_BIN" -u root <<'SQL'
ALTER USER 'root'@'localhost' IDENTIFIED VIA mysql_native_password USING PASSWORD('');
FLUSH PRIVILEGES;
SQL
fi

# --- 7. Quick sanity check: can we actually connect as the app would? ----
if "$MYSQL_BIN" -u root -e "SELECT 1;" >/dev/null 2>&1; then
    echo "✅ MariaDB is accepting password connections as root."
else
    echo "⚠️  Could not connect as root with empty password. phpMyAdmin may still fail."
    echo "    Try: sudo $MYSQL_BIN -u root   then run the ALTER USER block manually."
fi

# --- 8. Start the rest of XAMPP (Apache, etc.) ---------------------------
echo "🚀 Starting XAMPP..."
sudo "$XAMPP" start

echo "✨ Done. Try http://localhost/ and http://localhost/phpmyadmin/"