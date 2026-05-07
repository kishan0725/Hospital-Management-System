#!/bin/bash
echo "Committing new changes"
USER_NAME="natalio-f-gomes"
USER_EMAIL="natalio.f.gomes@proton.me"

# Get current git config values
current_name=$(git config --global user.name)
current_email=$(git config --global user.email)

# Only set if different from desired values
if [ "$current_name" != "$USER_NAME" ]; then
    git config --global user.name "$USER_NAME"
    echo "Updated git user.name to: $USER_EMAIL"
fi

if [ "$current_email" != "$USER_EMAIL" ]; then
    git config --global user.email "$USER_EMAIL"
    echo "Updated git user.email to: $USER_EMAIL"
fi

git init
git add .
read -p "Enter commit message: " message
git commit -m "$message"
git push -u origin Natalio-Dev
