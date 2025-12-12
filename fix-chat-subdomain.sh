#!/bin/bash
# Automatic fix for chat.tossee.com subdomain
# Run this on VPS: bash fix-chat-subdomain.sh

set -e  # Exit on error

echo "🔧 Fixing chat.tossee.com configuration..."

# 1. Check if files are in place
echo "✅ Step 1: Verifying files..."
if [ ! -f ~/domains/chat.tossee.com/public_html/index.html ]; then
    echo "❌ Files not found! Run deployment first!"
    exit 1
fi

# 2. Find nginx/apache config
echo "✅ Step 2: Finding web server configuration..."

if [ -d /etc/nginx/sites-enabled ]; then
    WEB_SERVER="nginx"
    CONFIG_DIR="/etc/nginx/sites-enabled"
elif [ -d /etc/apache2/sites-enabled ]; then
    WEB_SERVER="apache"
    CONFIG_DIR="/etc/apache2/sites-enabled"
else
    echo "⚠️  Managed hosting detected (Hostinger)"
    echo "📧 Contact Hostinger support or use hPanel to configure chat.tossee.com subdomain"
    echo ""
    echo "Manual steps:"
    echo "1. Go to hPanel → Domains → Subdomains"
    echo "2. Ensure chat.tossee.com points to: ~/domains/chat.tossee.com/public_html"
    echo "3. Wait 5 minutes for changes to propagate"
    exit 0
fi

echo "✅ Detected: $WEB_SERVER"

# 3. Create nginx config if needed
if [ "$WEB_SERVER" = "nginx" ]; then
    echo "✅ Step 3: Creating nginx configuration..."

    sudo tee /etc/nginx/sites-available/chat.tossee.com > /dev/null <<'NGINXCONF'
server {
    listen 80;
    listen [::]:80;
    server_name chat.tossee.com;

    root /home/u234011694/domains/chat.tossee.com/public_html;
    index index.html index.htm index.php;

    location / {
        try_files $uri $uri/ =404;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
NGINXCONF

    # Enable site
    sudo ln -sf /etc/nginx/sites-available/chat.tossee.com /etc/nginx/sites-enabled/

    # Test and reload
    sudo nginx -t && sudo systemctl reload nginx

    echo "✅ Nginx configured and reloaded!"
fi

echo ""
echo "🎉 DONE! Test now:"
echo "   https://chat.tossee.com/"
echo "   https://chat.tossee.com/api/matching.php"
