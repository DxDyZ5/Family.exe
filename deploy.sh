#!/bin/bash

# DigitalOcean Deployment Script
echo "Starting DigitalOcean deployment..."

# Build and push Docker image
docker build -t family-vip-gallery .
docker tag family-vip-gallery your-docker-username/family-vip-gallery:latest
docker push your-docker-username/family-vip-gallery:latest

# Deploy to DigitalOcean
echo "Deploying to DigitalOcean..."

# Create deployment file
cat > digitalocean-deployment.yaml <<EOF
name: family-vip-gallery
services:
- name: web
  source_dir: /
  github:
    repo: DxDyZ5/Family.exe
    branch: main
  run_command: |
    docker build -t family-vip-gallery .
    docker run -d -p 80:80 --name family-vip-gallery family-vip-gallery
  instance_count: 1
  instance_size_slug: basic-xxs
  envs:
  - key: APP_ENV
    value: production
  - key: DB_HOST
    value: \${db.HOSTNAME}
  - key: DB_USERNAME
    value: \${db.USERNAME}
  - key: DB_PASSWORD
    value: \${db.PASSWORD}
    type: secret
  - key: DB_DATABASE
    value: \${db.DATABASE}
  - key: TELEGRAM_BOT_TOKEN
    value: "8572783983:AAHk6NkmX7gVcErIj9LAtpH79bKLAn5yXAQ"
  - key: TELEGRAM_ADMIN_CHAT_ID
    value: "5061353279"
  - key: SIGHTENGINE_USER
    value: "1992666454"
  - key: SIGHTENGINE_API_KEY
    value: "QPVkgVR9MY4udFUKrK4KLpBErqPEJYjk"

databases:
- name: db
  engine: MYSQL
  version: "8"
  size: db-s-1vcpu-1gb
EOF

echo "Deployment file created. Upload to DigitalOcean App Platform to deploy."
echo ""
echo "Steps to deploy on DigitalOcean:"
echo "1. Create a DigitalOcean account"
echo "2. Go to App Platform"
echo "3. Click 'Create App'"
echo "4. Connect your GitHub repo: DxDyZ5/Family.exe"
echo "5. Add MySQL database"
echo "6. Set environment variables"
echo "7. Deploy!"
