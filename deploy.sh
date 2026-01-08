#!/bin/bash

echo "Deploying FreshTracks to production..."

rsync -avz \
  --exclude 'node_modules' \
  --exclude '.nuxt' \
  --exclude '.output' \
  --exclude 'database/database.sqlite' \
  --exclude '.env' \
  --exclude 'storage/logs/*' \
  --exclude 'storage/framework/cache/*' \
  --exclude 'storage/framework/sessions/*' \
  "/Users/jordanplamondon/Documents/Recovered Documents/FreshTracks/fresh-tracks/" \
  fortify@165.22.229.33:/var/www/freshtracks/

echo "Fixing permissions..."
ssh -t fortify@165.22.229.33 "sudo chown -R fortify:www-data /var/www/freshtracks && sudo chmod -R 755 /var/www/freshtracks/app /var/www/freshtracks/vendor && sudo chmod -R 775 /var/www/freshtracks/storage /var/www/freshtracks/database && sudo chmod 664 /var/www/freshtracks/database/database.sqlite"

echo "Building frontend..."
ssh fortify@165.22.229.33 "source ~/.nvm/nvm.sh && cd /var/www/freshtracks/client && rm -rf .nuxt .output && npm run build && pm2 restart freshtracks"

echo "Deploy complete!"
