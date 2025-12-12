#!/bin/bash

# Deployment script for chat.tossee.com
# This copies files from Git repo to web server

echo "🚀 Deploying chat.tossee.com..."

# Define paths
SOURCE_DIR="./chat.tossee.com"
DEST_DIR="/var/www/chat.tossee.com"

# Create destination directories if they don't exist
echo "📁 Creating directories..."
sudo mkdir -p "$DEST_DIR/api"

# Copy files
echo "📋 Copying files..."
sudo cp "$SOURCE_DIR/index.html" "$DEST_DIR/"
sudo cp "$SOURCE_DIR/api/matching.php" "$DEST_DIR/api/"
sudo cp "$SOURCE_DIR/api/signaling.php" "$DEST_DIR/api/"
sudo cp "$SOURCE_DIR/session_config.php" "$DEST_DIR/"

# Set correct permissions
echo "🔒 Setting permissions..."
sudo chown -R www-data:www-data "$DEST_DIR"
sudo chmod 755 "$DEST_DIR"
sudo chmod 755 "$DEST_DIR/api"
sudo chmod 644 "$DEST_DIR"/*.html
sudo chmod 644 "$DEST_DIR"/*.php
sudo chmod 644 "$DEST_DIR/api"/*.php

echo "✅ Deployment complete!"
echo ""
echo "Files deployed to: $DEST_DIR"
echo ""
echo "🧪 Test URLs:"
echo "  - https://chat.tossee.com/index.html"
echo "  - https://chat.tossee.com/api/matching.php"
echo "  - https://chat.tossee.com/api/signaling.php"
