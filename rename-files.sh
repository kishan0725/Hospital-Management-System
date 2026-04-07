#!/bin/bash

OLD_NAMES=(
  "index.php" "index1.php" "func.php" "func1.php" "func2.php"
  "func3.php" "newfunc.php" "admin-panel.php" "admin-panel1.php"
  "doctor-panel.php" "prescribe.php" "appsearch.php" "patientsearch.php"
  "doctorsearch.php" "messearch.php" "logout.php" "logout1.php"
  "error.php" "error1.php" "error2.php"
)

NEW_NAMES=(
  "register.php" "patient_login.php" "patient_auth.php" "doctor_auth.php"
  "patient_register_handler.php" "admin_auth.php" "db_helpers.php"
  "patient_dashboard.php" "receptionist_dashboard.php" "doctor_dashboard.php"
  "prescription_form.php" "appointment_search.php" "patient_search.php"
  "doctor_search.php" "message_search.php" "patient_logout.php"
  "doctor_logout.php" "error_patient_login.php" "error_password_mismatch.php"
  "error_admin_login.php"
)

# ── 1. Rename files ───────────────────────────────────────────────────────
for i in "${!OLD_NAMES[@]}"; do
  OLD="${OLD_NAMES[$i]}"
  NEW="${NEW_NAMES[$i]}"
  if [ -f "$OLD" ]; then
    mv "$OLD" "$NEW"
    echo "Renamed: $OLD → $NEW"
  else
    echo "Skipped (not found): $OLD"
  fi
done

# ── 2. Update all references (macOS-compatible sed) ───────────────────────
for i in "${!OLD_NAMES[@]}"; do
  OLD="${OLD_NAMES[$i]}"
  NEW="${NEW_NAMES[$i]}"
  while IFS= read -r FILE; do
    sed -i '' "s|$OLD|$NEW|g" "$FILE"
  done < <(find . -maxdepth 1 \( -name "*.php" -o -name "*.html" \))
  echo "References updated: $OLD → $NEW"
done

echo "Done."