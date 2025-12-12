#!/bin/bash
# Simple nginx setup for chat.tossee.com
# Run on VPS: bash setup-chat-nginx.sh

echo "🔧 Setting up chat.tossee.com nginx configuration..."

# Create nginx config
cat > /etc/nginx/sites-available/chat.tossee.com << 'EOF'
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
EOF

echo "✅ Config created"

# Enable site
ln -sf /etc/nginx/sites-available/chat.tossee.com /etc/nginx/sites-enabled/
echo "✅ Site enabled"

# Test config
nginx -t
echo "✅ Config tested"

# Reload nginx
systemctl reload nginx
echo "✅ Nginx reloaded"

echo ""
echo "🎉 DONE! Test: https://chat.tossee.com/"
