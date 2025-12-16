#!/bin/bash
# Tossee MVP Deployment Script
# Deploys API files and configuration to Hostinger VPS

set -e  # Exit on error

SERVER="u234011694@62.72.34.8"
PORT="65002"
KEY="tossee-mvp-key.pem"

# Color output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}=== Tossee MVP Deployment ===${NC}\n"

# Check if key exists
if [ ! -f "$KEY" ]; then
    echo -e "${RED}❌ Error: SSH key not found: $KEY${NC}"
    echo "Please place the key in the current directory"
    exit 1
fi

echo -e "${YELLOW}Server: $SERVER (Port $PORT)${NC}\n"

# Test connection
echo "Testing SSH connection..."
if ! ssh -i "$KEY" -p "$PORT" "$SERVER" "echo '✅ Connection successful'" 2>/dev/null; then
    echo -e "${RED}❌ Cannot connect to server${NC}"
    exit 1
fi

echo ""

# Create directories on server
echo "Creating directories..."
ssh -i "$KEY" -p "$PORT" "$SERVER" <<'ENDSSH'
mkdir -p ~/domains/tossee.com/public_html/chat/api
mkdir -p ~/domains/tossee.com/public_html/wp-content/plugins
chmod 755 ~/domains/tossee.com/public_html/chat
chmod 755 ~/domains/tossee.com/public_html/chat/api
echo "✅ Directories created"
ENDSSH

echo ""

# Upload API files
echo "Uploading API files..."
scp -i "$KEY" -P "$PORT" \
    api/matching.php api/signaling.php \
    "$SERVER:~/domains/tossee.com/public_html/chat/api/"

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ API files uploaded${NC}"
else
    echo -e "${RED}❌ Failed to upload API files${NC}"
    exit 1
fi

echo ""

# Upload theme fix script
echo "Uploading Astra theme fix script..."
scp -i "$KEY" -P "$PORT" \
    fix-astra-theme.php \
    "$SERVER:~/domains/tossee.com/public_html/"

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ Fix script uploaded${NC}"
else
    echo -e "${RED}❌ Failed to upload fix script${NC}"
fi

echo ""

# Set permissions
echo "Setting file permissions..."
ssh -i "$KEY" -p "$PORT" "$SERVER" <<'ENDSSH'
chmod 644 ~/domains/tossee.com/public_html/chat/api/*.php
chmod 644 ~/domains/tossee.com/public_html/fix-astra-theme.php
echo "✅ Permissions set"
ENDSSH

echo ""

# Create .htaccess for chat directory
echo "Creating .htaccess for chat directory..."
ssh -i "$KEY" -p "$PORT" "$SERVER" <<'ENDSSH'
cat > ~/domains/tossee.com/public_html/chat/.htaccess <<'EOF'
# Disable WordPress URL rewriting for chat directory
RewriteEngine Off

# Allow direct access to all files
<FilesMatch "\.(php|html|js|css|json)$">
    Require all granted
</FilesMatch>

# Enable CORS for API
<IfModule mod_headers.c>
    Header set Access-Control-Allow-Origin "*"
    Header set Access-Control-Allow-Methods "POST, GET, OPTIONS"
    Header set Access-Control-Allow-Headers "Content-Type"
</IfModule>
EOF
echo "✅ .htaccess created"
ENDSSH

echo ""

# Create .htaccess for API directory
echo "Creating .htaccess for API directory..."
ssh -i "$KEY" -p "$PORT" "$SERVER" <<'ENDSSH'
cat > ~/domains/tossee.com/public_html/chat/api/.htaccess <<'EOF'
# Direct access to PHP files (no WordPress routing)
<IfModule mod_rewrite.c>
    RewriteEngine Off
</IfModule>

# Allow execution
<FilesMatch "\.php$">
    Require all granted
</FilesMatch>

# CORS headers
<IfModule mod_headers.c>
    Header set Access-Control-Allow-Origin "*"
    Header set Access-Control-Allow-Methods "POST, OPTIONS"
    Header set Access-Control-Allow-Headers "Content-Type"
</IfModule>
EOF
echo "✅ API .htaccess created"
ENDSSH

echo ""

# Verify files exist
echo "Verifying deployment..."
ssh -i "$KEY" -p "$PORT" "$SERVER" <<'ENDSSH'
echo "Checking API files:"
ls -lh ~/domains/tossee.com/public_html/chat/api/
echo ""
echo "Checking .htaccess files:"
ls -lh ~/domains/tossee.com/public_html/chat/.htaccess
ls -lh ~/domains/tossee.com/public_html/chat/api/.htaccess
ENDSSH

echo ""
echo -e "${GREEN}=== Deployment Complete ===${NC}\n"

echo "Next steps:"
echo "1. Fix Astra theme: https://tossee.com/fix-astra-theme.php"
echo "2. Test API endpoints:"
echo "   curl -X POST https://chat.tossee.com/api/matching.php \\"
echo "     -H 'Content-Type: application/json' \\"
echo "     -d '{\"userId\":\"test\",\"action\":\"join\"}'"
echo ""
echo "3. Restore plugins via wp-admin (avoid peters-login-redirect!)"
echo "4. Test chat functionality at https://chat.tossee.com/"

echo ""
echo -e "${YELLOW}⚠️  Remember to delete fix-astra-theme.php after use!${NC}"
