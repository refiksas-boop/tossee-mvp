#!/bin/bash
# RESTORE TOSSEE - Grąžinti viską atgal kaip buvo

SERVER="u234011694@62.72.34.8"
PORT="65002"
KEY="tossee-mvp-key.pem"

echo "🔄 Grąžiname viską atgal..."
echo ""

# Check if we can connect via key, otherwise will prompt for password
if [ -f "$KEY" ]; then
    SSH_CMD="ssh -i $KEY -p $PORT $SERVER"
    SCP_CMD="scp -i $KEY -P $PORT"
else
    echo "⚠️  SSH raktas nerastas - naudosime slaptažodį"
    SSH_CMD="ssh -p $PORT $SERVER"
    SCP_CMD="scp -P $PORT"
fi

echo "Jungiamės prie serverio..."
$SSH_CMD <<'ENDSSH'

cd /home/u234011694/domains/tossee.com/public_html/wp-content/

echo "1. Grąžiname plugins..."
if [ -d "plugins.DISABLED" ]; then
    # Backup current empty plugins if it exists
    if [ -d "plugins" ]; then
        rm -rf plugins.BACKUP 2>/dev/null
        mv plugins plugins.BACKUP
    fi
    # Restore original plugins
    mv plugins.DISABLED plugins
    echo "✅ Plugins grąžinti"
else
    echo "⚠️  plugins.DISABLED nerastas"
fi

echo ""
echo "2. Grąžiname Astra temą..."
cd themes/
if [ -d "astra.DISABLED" ]; then
    # Remove current astra if exists (it's the "fixed" version)
    if [ -d "astra" ]; then
        rm -rf astra.BACKUP 2>/dev/null
        mv astra astra.BACKUP
    fi
    # Restore original
    mv astra.DISABLED astra
    echo "✅ Astra tema grąžinta"
else
    echo "⚠️  astra.DISABLED nerastas"
fi

echo ""
echo "3. Tikriname failų teises..."
cd /home/u234011694/domains/tossee.com/public_html/
chmod 755 wp-content/plugins
chmod 755 wp-content/themes
echo "✅ Teisės patikrintos"

echo ""
echo "=== ATSTATYMAS BAIGTAS ==="
echo ""
echo "Dabar:"
echo "1. Atidaryti: https://tossee.com/"
echo "2. Turėtų veikti kaip anksčiau"
echo ""

ENDSSH

if [ $? -eq 0 ]; then
    echo ""
    echo "✅ VISKAS GRĄŽINTA ATGAL!"
    echo ""
    echo "Patikrinkite: https://tossee.com/"
else
    echo ""
    echo "❌ Nepavyko prisijungti prie serverio"
    echo ""
    echo "Galite atstatyti rankiniu būdu:"
    echo ""
    echo "ssh -p 65002 u234011694@62.72.34.8"
    echo "cd /home/u234011694/domains/tossee.com/public_html/wp-content/"
    echo "mv plugins.DISABLED plugins"
    echo "cd themes/"
    echo "mv astra.DISABLED astra"
    echo ""
fi
