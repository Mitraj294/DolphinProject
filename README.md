# Dolphin Full Stack Project

This repository contains both the backend (Laravel) and frontend (Vue.js) applications for the Dolphin project.

## Structure

```
Dolphin_Backend/   # Laravel PHP backend
Dolphin_Frontend/  # Vue.js frontend
```

- Use the provided `start-dev.sh` script to run both servers for development.
- See individual README files in each folder for more details.

## Quick Start

1. Install dependencies for both projects:
   - Backend: `composer install`
   - Frontend: `npm install`
2. Start both servers:
   - Run `./start-dev.sh` from the root directory.

## Development URLs
- Backend: http://127.0.0.1:8000
- Frontend: http://127.0.0.1:8080

## About
This project is a full-stack web application for assessment and lead management, built with Laravel and Vue.js.

sdolphin632@gmail.com

git add .
git commit -m "daily update"
git push


//////////////

./start-dev.sh
ngrok http 8000
 mysql -u  dolphin123 -p
SELECT * FROM `oauth_access_tokens` 
 cd Dolphin_Backend
 php artisan queue:work
 php artisan schedule:work



///////////////////
if by mistake we lost clients data in
"oauth_clients" table then run below commands

first
cd /home/digilab/Dolphin/Dolphin_Backend
php artisan passport:client --personal --name="Dolphin Personal Access Client"
php artisan passport:client --password --name="Dolphin Password Grant Client"

then# Replace with the values printed above
echo 'PASSPORT_PASSWORD_CLIENT_ID=YOUR_CLIENT_ID' >> .env
echo 'PASSPORT_PASSWORD_CLIENT_SECRET=YOUR_CLIENT_SECRET' >> .env

and clear caches
php artisan config:clear && php artisan cache:clear && php artisan optimize:clear
///////////////////




notifications date wise filter
Notification VIEW DETAILS PAGE

use

modelValue	string	
null
Value of the content.

name	parameters	returnType	description	
update:modelValue	
value: string
void
Emitted when the value changes.

name	type	description
htmlValue	string	Current value as html.