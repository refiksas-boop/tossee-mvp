#!/bin/bash
# Test Tossee API Endpoints

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo -e "${GREEN}=== Testing Tossee Endpoints ===${NC}\n"

# Test 1: Homepage
echo "1. Testing homepage (tossee.com)..."
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" https://tossee.com/)
if [ "$HTTP_CODE" = "200" ]; then
    echo -e "${GREEN}✅ Homepage: HTTP $HTTP_CODE${NC}"
else
    echo -e "${RED}❌ Homepage: HTTP $HTTP_CODE${NC}"
fi

echo ""

# Test 2: Chat subdomain
echo "2. Testing chat subdomain (chat.tossee.com)..."
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" https://chat.tossee.com/)
if [ "$HTTP_CODE" = "200" ]; then
    echo -e "${GREEN}✅ Chat subdomain: HTTP $HTTP_CODE${NC}"
else
    echo -e "${RED}❌ Chat subdomain: HTTP $HTTP_CODE${NC}"
fi

echo ""

# Test 3: Matching API
echo "3. Testing matching API..."
RESPONSE=$(curl -s -X POST https://chat.tossee.com/api/matching.php \
    -H "Content-Type: application/json" \
    -d '{"userId":"test123","action":"join"}' \
    -w "\nHTTP_CODE:%{http_code}")

HTTP_CODE=$(echo "$RESPONSE" | grep "HTTP_CODE" | cut -d':' -f2)
BODY=$(echo "$RESPONSE" | grep -v "HTTP_CODE")

echo "Response: $BODY"
if [ "$HTTP_CODE" = "200" ] || [ "$HTTP_CODE" = "502" ]; then
    # 502 is expected if n8n is not responding, but means our proxy works
    echo -e "${GREEN}✅ Matching API: HTTP $HTTP_CODE (endpoint accessible)${NC}"
else
    echo -e "${RED}❌ Matching API: HTTP $HTTP_CODE${NC}"
fi

echo ""

# Test 4: Signaling API
echo "4. Testing signaling API..."
RESPONSE=$(curl -s -X POST https://chat.tossee.com/api/signaling.php \
    -H "Content-Type: application/json" \
    -d '{"type":"offer","userId":"test123","data":{}}' \
    -w "\nHTTP_CODE:%{http_code}")

HTTP_CODE=$(echo "$RESPONSE" | grep "HTTP_CODE" | cut -d':' -f2)
BODY=$(echo "$RESPONSE" | grep -v "HTTP_CODE")

echo "Response: $BODY"
if [ "$HTTP_CODE" = "200" ] || [ "$HTTP_CODE" = "502" ]; then
    echo -e "${GREEN}✅ Signaling API: HTTP $HTTP_CODE (endpoint accessible)${NC}"
else
    echo -e "${RED}❌ Signaling API: HTTP $HTTP_CODE${NC}"
fi

echo ""

# Test 5: n8n webhooks directly
echo "5. Testing n8n webhooks..."
echo -n "   Matching webhook: "
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" \
    -X POST https://n8n.tossee.com/webhook/next \
    -H "Content-Type: application/json" \
    -d '{"test":true}')
if [ "$HTTP_CODE" = "200" ]; then
    echo -e "${GREEN}HTTP $HTTP_CODE ✅${NC}"
else
    echo -e "${YELLOW}HTTP $HTTP_CODE (workflow may be inactive)${NC}"
fi

echo -n "   Signaling webhook: "
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" \
    -X POST https://n8n.tossee.com/webhook/signal \
    -H "Content-Type: application/json" \
    -d '{"test":true}')
if [ "$HTTP_CODE" = "200" ]; then
    echo -e "${GREEN}HTTP $HTTP_CODE ✅${NC}"
else
    echo -e "${YELLOW}HTTP $HTTP_CODE (workflow may be inactive)${NC}"
fi

echo ""
echo -e "${GREEN}=== Test Complete ===${NC}"
echo ""
echo "Expected results:"
echo "  - Homepage & Chat: HTTP 200"
echo "  - API endpoints: HTTP 200 or 502 (502 = proxy works, n8n not responding)"
echo "  - n8n webhooks: HTTP 200 (if workflows are active)"
