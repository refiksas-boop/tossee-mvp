#!/bin/bash
# Deployment script for chat.tossee.com VPS
# Run this on your LOCAL machine, NOT on VPS

echo "🚀 Deploying chat.tossee.com to VPS..."
echo ""

# VPS SSH details
VPS_USER="u234011694"
VPS_HOST="62.72.34.8"
VPS_PORT="65002"
VPS_PATH="/var/www/tossee.com"  # Adjust if needed

# Local files
LOCAL_DIR="./chat.tossee.com"

echo "📤 Step 1: Creating directory on VPS..."
ssh -p $VPS_PORT $VPS_USER@$VPS_HOST "mkdir -p $VPS_PATH/chat/api"

echo "📤 Step 2: Uploading index.html..."
scp -P $VPS_PORT "$LOCAL_DIR/index.html" $VPS_USER@$VPS_HOST:$VPS_PATH/chat/

echo "📤 Step 3: Uploading matching.php..."
scp -P $VPS_PORT "$LOCAL_DIR/api/matching.php" $VPS_USER@$VPS_HOST:$VPS_PATH/chat/api/

echo "📤 Step 4: Uploading signaling.php..."
scp -P $VPS_PORT "$LOCAL_DIR/api/signaling.php" $VPS_USER@$VPS_HOST:$VPS_PATH/chat/api/

echo "🔒 Step 5: Setting permissions..."
ssh -p $VPS_PORT $VPS_USER@$VPS_HOST "chmod 644 $VPS_PATH/chat/index.html && chmod 644 $VPS_PATH/chat/api/*.php"

echo ""
echo "✅ Deployment complete!"
echo ""
echo "🧪 Test URLs:"
echo "  - https://chat.tossee.com/index.html"
echo "  - https://chat.tossee.com/api/matching.php"
echo ""
echo "⚠️  IMPORTANT: Make sure chat.tossee.com subdomain is configured!"
echo "    DNS: chat.tossee.com → 62.72.34.8"
echo "    Web server: Virtual host pointing to $VPS_PATH/chat/"
