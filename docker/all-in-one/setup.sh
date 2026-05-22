#!/bin/sh
# TitaKita all-in-one setup helper
# ---------------------------------
# Creates a local .env from .env.example and auto-generates the two required
# secrets (APP_KEY and JWT_SECRET) if they are still empty, so a first-time
# operator can get a node running without any manual key generation.
#
# Usage:
#   cd docker/all-in-one
#   ./setup.sh
#   docker compose up -d
#
# Requires: openssl (preinstalled on Linux/macOS/WSL/Git-Bash).

set -e
cd "$(dirname "$0")"

if ! command -v openssl >/dev/null 2>&1; then
    echo "ERROR: openssl is required but was not found on PATH."
    echo "Install openssl, or generate APP_KEY / JWT_SECRET manually (see README.md)."
    exit 1
fi

if [ ! -f .env ]; then
    cp .env.example .env
    echo "Created .env from .env.example"
else
    echo ".env already exists - leaving it in place"
fi

# Generate APP_KEY (Laravel expects the 'base64:' prefix) only if empty.
if grep -qE '^APP_KEY=$' .env; then
    APP_KEY_VALUE="base64:$(openssl rand -base64 32)"
    sed -i.bak "s|^APP_KEY=.*|APP_KEY=${APP_KEY_VALUE}|" .env && rm -f .env.bak
    echo "Generated APP_KEY"
else
    echo "APP_KEY already set - skipping"
fi

# Generate JWT_SECRET only if empty.
if grep -qE '^JWT_SECRET=$' .env; then
    JWT_SECRET_VALUE="$(openssl rand -base64 32)"
    sed -i.bak "s|^JWT_SECRET=.*|JWT_SECRET=${JWT_SECRET_VALUE}|" .env && rm -f .env.bak
    echo "Generated JWT_SECRET"
else
    echo "JWT_SECRET already set - skipping"
fi

echo ""
echo "Setup complete."
echo ""
echo "Next steps:"
echo "  1. (Production) edit .env and set your real domain in VITE_FRONTEND_URL /"
echo "     APP_FRONTEND_URL / APP_CDN_URL, plus Stripe + mail credentials."
echo "  2. Start TitaKita:   docker compose up -d"
echo "  3. Open:             http://localhost:8123/auth/register"
echo ""
echo "Leaving the defaults gives you a no-external-accounts 'try it out' node"
echo "(mail is written to logs, payments disabled)."
