#!/usr/bin/env bash
# Quick deployment check script

echo "=== Dolphin Render Deployment Checklist ==="
echo ""

# Check if required files exist
echo "✓ Checking deployment files..."
[ -f "render.yaml" ] && echo "  ✓ render.yaml found" || echo "  ✗ render.yaml missing"
[ -f "Dolphin_Backend/build.sh" ] && echo "  ✓ build.sh found" || echo "  ✗ build.sh missing"
[ -f "Dolphin_Backend/start.sh" ] && echo "  ✓ start.sh found" || echo "  ✗ start.sh missing"
[ -x "Dolphin_Backend/build.sh" ] && echo "  ✓ build.sh is executable" || echo "  ✗ build.sh not executable"
[ -x "Dolphin_Backend/start.sh" ] && echo "  ✓ start.sh is executable" || echo "  ✗ start.sh not executable"

echo ""
echo "✓ Checking Laravel configuration..."
[ -f "Dolphin_Backend/composer.json" ] && echo "  ✓ composer.json found" || echo "  ✗ composer.json missing"
[ -f "Dolphin_Backend/.env.example" ] && echo "  ✓ .env.example found" || echo "  ✗ .env.example missing"

echo ""
echo "=== Next Steps ==="
echo "1. Review DEPLOYMENT.md for detailed instructions"
echo "2. Push your code to GitHub/GitLab/Bitbucket"
echo "3. Create a new Web Service on Render from your repository"
echo "4. Render will automatically detect render.yaml"
echo "5. Update environment variables in Render Dashboard"
echo "6. Update frontend to point to your Render backend URL"
echo ""
echo "For more information, see: DEPLOYMENT.md"
