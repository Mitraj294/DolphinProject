# Dolphin Frontend

This directory contains the Vue.js single-page application (SPA) that serves as the frontend for the Dolphin project. It provides a modern, responsive user interface for interacting with the backend API.

## Tech Stack

-   **Framework**: Vue.js
-   **Routing**: Vue Router
-   **State Management**: Implicit via component state and props (no global state manager like Vuex)
-   **HTTP Client**: Axios
-   **UI Components**: PrimeVue and custom-built components

## Features

-   User-friendly interface for all application features.
-   Dynamic forms for registration, lead capture, and assessments.
-   Interactive tables with sorting, pagination, and filtering.
-   Role-based views for regular users and superadmins.
-   Responsive design for usability across different screen sizes.

## Getting Started

### 1. Prerequisites

-   Node.js and npm

### 2. Installation

From within the `Dolphin_Frontend` directory:

```bash
# Install JavaScript dependencies
npm install
```

### 3. Environment Configuration

Create a `.env.local` file in the `Dolphin_Frontend` directory to override default environment variables. At a minimum, you should specify the backend API URL.

```ini
# .env.local
VUE_APP_API_BASE_URL=http://127.0.0.1:8000
```

### 4. Running the Development Server

```bash
# Start the Vue.js development server
npm run serve
```

The frontend application will be available at `http://localhost:8080`.

## Building for Production

To create a production-ready build of the application:

```bash
# Compile and minify for production
npm run build
```

The output will be generated in the `dist/` directory and can be served by any static file server.

## Key Directories

-   `src/components`: Contains reusable Vue components, organized by feature area.
-   `src/router`: Defines the application's routes and navigation guards.
-   `src/services`: Includes modules for interacting with the backend API (e.g., `auth.js`, `storage.js`).
-   `src/views`: Top-level components that correspond to specific pages/routes.
-   `src/layout`: Components that define the main structure of the application, like `MainLayout.vue`.
-   `src/assets`: Stores static assets like images, fonts, and global CSS.
-   `public/`: Contains the root `index.html` file and other assets that are copied directly to the build output.

# Dolphin Frontend (Vue.js)

This is the frontend application for the Dolphin project, built with Vue.js.

## Features
- SPA (Single Page Application)
- Vue Router for navigation
- API integration with Dolphin backend
- Modular components and services
- Responsive layout

## Setup
1. Install dependencies:
   ```bash
   npm install
   ```
2. Configure environment variables in `.env` (e.g., API base URL).
3. Start the development server:
   ```bash
   npm run serve -- --host 127.0.0.1
   ```

## Folder Structure
- `src/components`: Vue components
- `src/router`: Vue Router setup
- `src/services`: API calls
- `public/`: Static files

## Development
- Make sure the backend is running for API calls.
- Use the provided `start-dev.sh` script in the root to run both servers together.

## License
MIT

## Installation
1. Clone the repository:
   ```
   git clone https://github.com/Mitraj294/Dolphin.git
   cd Dolphin
   ```
2. Install dependencies:
   ```
   npm install
   ```

## Usage
To run the application in development mode:
```
npm run serve
```
Visit `http://localhost:8080` in your browser.

## Contributing
Contributions are welcome! Please open an issue or submit a pull request for suggestions or improvements.

## License
This project is licensed under the MIT License.


////////////


/home/digilab/Dolphin/src/components/Common/Organizations/OrganizationTable.vue
this is perfect look att this file we need our all the files like this we need
this type of perfect
page contet
for our this file make this 

leads

 file just like perefcty like this

also look how just its only table part is scorable all other fiexed and resonsive make good responsive percet file like that


so basic stucture and all things fix  like this
it's data and conenet will just as it is we need to fix stuctute cause we need same type of pages across app

# Start Vue.js frontend
(lsof -ti:8080 | xargs -r kill)
(cd src && npm run serve -- --port 8080 &)
