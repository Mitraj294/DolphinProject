#!/bin/bash

# Dolphin Project - Quick Deployment Script
# This script helps prepare your project for deployment

echo "======================================"
echo "  Dolphin Deployment Preparation"
echo "======================================"
echo ""

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if we're in the right directory
if [ ! -d "Dolphin_Backend" ] || [ ! -d "Dolphin_Frontend" ]; then
    echo -e "${RED}Error: This script must be run from the project root directory${NC}"
    exit 1
fi

echo -e "${GREEN}✓${NC} Project structure detected"
echo ""

# Step 1: Check Backend
echo "1. Checking Backend Setup..."
cd Dolphin_Backend

if [ ! -f "composer.json" ]; then
    echo -e "${RED}✗ composer.json not found${NC}"
    exit 1
fi
echo -e "${GREEN}  ✓ composer.json found${NC}"

if [ ! -f ".env.example" ]; then
    echo -e "${YELLOW}  ⚠ .env.example not found${NC}"
else
    echo -e "${GREEN}  ✓ .env.example found${NC}"
fi

if [ ! -f "Dockerfile" ]; then
    echo -e "${RED}  ✗ Dockerfile not found${NC}"
    exit 1
fi
echo -e "${GREEN}  ✓ Dockerfile found${NC}"

cd ..

# Step 2: Check Frontend
echo ""
echo "2. Checking Frontend Setup..."
cd Dolphin_Frontend

if [ ! -f "package.json" ]; then
    echo -e "${RED}✗ package.json not found${NC}"
    exit 1
fi
echo -e "${GREEN}  ✓ package.json found${NC}"

if [ ! -f "netlify.toml" ]; then
    echo -e "${RED}  ✗ netlify.toml not found${NC}"
    exit 1
fi
echo -e "${GREEN}  ✓ netlify.toml found${NC}"

if [ ! -f "public/_redirects" ]; then
    echo -e "${YELLOW}  ⚠ _redirects file not found${NC}"
else
    echo -e "${GREEN}  ✓ _redirects found${NC}"
fi

cd ..

# Step 3: Check deployment configs
echo ""
echo "3. Checking Deployment Configuration..."

if [ ! -f "render.yaml" ]; then
    echo -e "${RED}✗ render.yaml not found${NC}"
    exit 1
fi
echo -e "${GREEN}  ✓ render.yaml found${NC}"

if [ ! -f "DEPLOYMENT_GUIDE.md" ]; then
    echo -e "${YELLOW}  ⚠ DEPLOYMENT_GUIDE.md not found${NC}"
else
    echo -e "${GREEN}  ✓ DEPLOYMENT_GUIDE.md found${NC}"
fi

# Step 4: Generate APP_KEY
echo ""
echo "4. Generating Laravel APP_KEY..."
cd Dolphin_Backend

if command -v php &> /dev/null && command -v composer &> /dev/null; then
    echo ""
    echo -e "${YELLOW}Copy this APP_KEY to Render environment variables:${NC}"
    echo ""
    php artisan key:generate --show
    echo ""
else
    echo -e "${YELLOW}  ⚠ PHP or Composer not found locally${NC}"
    echo -e "  You can generate APP_KEY after deploying to Render"
fi

cd ..

# Step 5: Git status
echo ""
echo "5. Checking Git Status..."
if [ -d ".git" ]; then
    echo -e "${GREEN}  ✓ Git repository detected${NC}"
    
    # Check if there are uncommitted changes
    if [[ -n $(git status -s) ]]; then
        echo -e "${YELLOW}  ⚠ You have uncommitted changes${NC}"
        echo ""
        echo "  Uncommitted files:"
        git status -s | head -10
        echo ""
        echo -e "${YELLOW}  Consider committing these before deployment${NC}"
    else
        echo -e "${GREEN}  ✓ Working directory clean${NC}"
    fi
    
    # Check remote
    if git remote -v | grep -q "origin"; then
        echo -e "${GREEN}  ✓ Remote repository configured${NC}"
        git remote -v | head -2
    else
        echo -e "${YELLOW}  ⚠ No remote repository configured${NC}"
    fi
else
    echo -e "${RED}  ✗ Not a git repository${NC}"
    echo "  Initialize git and push to GitHub before deploying"
fi

# Summary
echo ""
echo "======================================"
echo "  Next Steps"
echo "======================================"
echo ""
echo "1. ${GREEN}Push to GitHub:${NC}"
echo "   git add ."
echo "   git commit -m 'Prepare for deployment'"
echo "   git push origin main"
echo ""
echo "2. ${GREEN}Deploy Backend (Render):${NC}"
echo "   • Go to https://render.com"
echo "   • Create new Blueprint"
echo "   • Connect your GitHub repository"
echo "   • Configure environment variables"
echo ""
echo "3. ${GREEN}Deploy Frontend (Netlify):${NC}"
echo "   • Go to https://netlify.com"
echo "   • Import project from GitHub"
echo "   • Set base directory: Dolphin_Frontend"
echo "   • Configure environment variables"
echo ""
echo "4. ${GREEN}Read the deployment guide:${NC}"
echo "   cat DEPLOYMENT_GUIDE.md"
echo ""
echo "======================================"
echo ""

echo -e "${GREEN}✓ Pre-deployment check complete!${NC}"
echo ""
