# Tinkerlist Episode and Part Management API

This project is a Laravel-based API for managing episodes and their parts. It provides CRUD operations for episodes and parts, as well as the ability to update part positions dynamically.

---

## Features

- CRUD for **Episodes**.
- CRUD for **Parts** linked to Episodes.
- Dynamic position management for Parts.
- Validation for unique titles and positional updates.
- Feature tests for all endpoints.
- **Swagger API Documentation** for easy API exploration.

---

## Requirements

- PHP 8.1 or higher
- Composer
- MySQL or another database supported by Laravel
- Node.js & npm (for frontend or additional tooling, if needed)
- Postman or any API testing tool (optional)

---

## Installation

### Step 1: Clone the Repository
```bash
git clone https://github.com/your-repository/episode-part-management.git
cd episode-part-management
```

### Step 2: Install Dependencies
```bash
composer install
npm install
```

### Step 3: Configure Environment
Copy the `.env.example` file to `.env`:
```bash
cp .env.example .env
```

Edit the `.env` file and set up your database and other configurations:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password
```

### Step 4: Generate Application Key
```bash
php artisan key:generate
```

### Step 5: Run Migrations and Seed the Database
```bash
php artisan migrate --seed
```

---

## Running the Application

### Start the Development Server
```bash
php artisan serve
```

The application will be available at `http://127.0.0.1:8000`.

### Access Swagger API Documentation
Visit [http://127.0.0.1:8000/api-documentation](http://127.0.0.1:8000/api-documentation) to explore the API using Swagger UI.

---

## API Endpoints

### Base URL
```
http://127.0.0.1:8000/api
```

### Endpoints

#### Episode Endpoints
| Method | Endpoint         | Description             |
| ------ | ---------------- | ----------------------- |
| POST   | `/episodes`      | Create a new episode.   |
| GET    | `/episodes`      | Get a list of episodes. |
| GET    | `/episodes/{id}` | Get a specific episode. |
| PUT    | `/episodes/{id}` | Update an episode.      |
| DELETE | `/episodes/{id}` | Delete an episode.      |

#### Part Endpoints
| Method | Endpoint                                       | Description                           |
| ------ | ---------------------------------------------- | ------------------------------------- |
| POST   | `/episode/{episode_id}/part`                   | Add a part to an episode.             |
| GET    | `/episode/{episode_id}/parts`                  | Get all parts of an episode.          |
| POST   | `/episode/{episode_id}/parts/{part_id}`        | Update a specific part in an episode. |
| DELETE | `/episode/{episode_id}/parts/{part_id}`        | Delete a specific part in an episode. |
| POST   | `/episode/{episode_id}/parts/update/positions` | Update part positions.                |
| POST   | `/episode/{episode_id}/parts/reorder`          | Reorder part positions dynamically.   |


---

## Running Tests

### Run Unit and Feature Tests
To run all tests, execute:
```bash
php artisan test
```

### Expected Output
The tests will validate:
- CRUD functionality for Episodes and Parts.
- Position recalculation logic.
- Input validations and unique title enforcement.

---

## Testing APIs

You can use tools like **Swagger**, 

-------------------------------------------

## Swagger Documentation

The API comes with a Swagger UI for exploring and testing endpoints.

### Access Swagger
Visit [http://127.0.0.1:8000/api-documentation](http://127.0.0.1:8000/api-documentation) after starting the server.

---

## Troubleshooting

- **Database Errors**: Ensure the `.env` file is properly configured, and migrations have been run.
- **Validation Errors**: Check the API response messages for validation rules.
- **Permission Errors**: Ensure the necessary file permissions for Laravel (`storage/` and `bootstrap/cache`).

---

## License
This project is open-source and available under the [MIT License](LICENSE).

--- 

Feel free to contribute or report issues! 😊
