#!/usr/bin/env bash
# Quick deployment check script

echo "=== Dolphin Render Deployment Checklist ==="
echo ""

# Check if required files exist
echo "✓ Checking deployment files..."
[ -f "render.yaml" ] && echo "  ✓ render.yaml found" || echo "  ✗ render.yaml missing"
[ -f "Dockerfile" ] && echo "  ✓ Dockerfile found" || echo "  ✗ Dockerfile missing"
[ -f "docker-entrypoint.sh" ] && echo "  ✓ docker-entrypoint.sh found" || echo "  ✗ docker-entrypoint.sh missing"
[ -x "docker-entrypoint.sh" ] && echo "  ✓ docker-entrypoint.sh is executable" || echo "  ✗ docker-entrypoint.sh not executable"
[ -f ".dockerignore" ] && echo "  ✓ .dockerignore found" || echo "  ✗ .dockerignore missing"
[ -f "Dolphin_Backend/build.sh" ] && echo "  ✓ build.sh found (backup)" || echo "  ⚠ build.sh missing"
[ -f "Dolphin_Backend/start.sh" ] && echo "  ✓ start.sh found (backup)" || echo "  ⚠ start.sh missing"

echo ""
echo "✓ Checking Laravel configuration..."
[ -f "Dolphin_Backend/composer.json" ] && echo "  ✓ composer.json found" || echo "  ✗ composer.json missing"
[ -f "Dolphin_Backend/.env.example" ] && echo "  ✓ .env.example found" || echo "  ✗ .env.example missing"

echo ""
echo "=== Next Steps ==="
echo "1. Commit changes: git add . && git commit -m 'Add Docker for Render'"
echo "2. Push to repository: git push origin main"
echo "3. In Render Dashboard, click 'Manual Deploy' → 'Clear build cache & deploy'"
echo "4. Update environment variables (especially APP_KEY, APP_URL, FRONTEND_URL)"
echo "5. Wait for deployment to complete"
echo "6. Test: https://your-app.onrender.com/api/health"
echo "7. Update frontend VUE_APP_API_BASE_URL in Netlify"
echo ""
echo "For detailed instructions, see: DEPLOYMENT.md"
