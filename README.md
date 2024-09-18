This document provides an overview of the available API endpoints, their usage, and guidelines on how to test them.

# ⚠️ API Important considerations

- In the `GET` endpoints $${\color{yellow}we \space don't \space  use \space pagination \space in \space order \space to \space keep \space this \space POC \space simple}$$ , but in a real project we should implement it.

- We suppose that each product must have a `category`, so  $${\color{yellow}first \space you \space need \space to \space create \space a \space category \space before \space creating \space a \space product}$$.

- The Many-To-One relation between `Category` and `Product` is implemented with `ON DELETE CASCADE`, so be carefull when deleting a category, $${\color{yellow}all \space the \space products \space associated \space with \space it \space will \space be \space deleted \space as \space well}$$.

## 🔑 Token-Based Authentication

All endpoints require a valid authentication token. Include the token in the `Authorization` header as follows:

```bash

Authorization: Bearer mY-Very-Secret-Token
```

For the sake of simplicity, we use the `.env` file to store the API Token in the `API_TOKEN` variable.

```bash

API_TOKEN=mY-Very-Secret-Token
```

# 🔥 Project setup

<details>
<summary><strong>👀 Installing & Setting up the Symfony Framework</strong></summary>

Do this in case you don't have PHP 8.x installed, Composer and Symfony CLI installed.

### 1. Install PHP if you don't have PHP 8.x installed

1. Visit the [PHP official website](https://www.php.net/downloads) and download the latest stable Thread Safe PHP version.

2. Windows: Create a new folder `C:\php` and extract the downloaded PHP files there.

### 2. Install Composer, which is used to install PHP packages.

Follow the official [Composer installation guide](https://getcomposer.org/download/).

### 3. Install Symfony CLI.

Follow the official [Symfony CLI installation guide](https://symfony.com/download).

</details>

## Clone the Symfony project locally

1. Clone the repository to your local machine using the following command:

```bash
git clone https://github.com/SrVladyslav/symfony_crud_poc_backend.git
```

2. Navigate to the project directory and install the required dependencies using Composer:

```bash
cd symfony_crud_poc
composer install
```

3. Go to the `.env` file in the project root directory and add the following content:

```bash
API_TOKEN=mY-Very-Secret-Token

DATABASE_URL="sqlite:///C:/<PATH_TO_PROJECT>/symfony_crud_poc/var/data.db"
```

As you can see, we are using a SQLite database for the local project just to keep it simple, but you can also try Supabase.

## Database configuration

First let's set permissions for the var directory

```bash
chmod -R 755 var
chmod -R 777 var
```

### Prior configurations

Before creating the Database, you should choose the DB to use, so in our case will be the SQLite, but you can use other ones if you want. 

1. Let's config the doctrine yaml file, in your project open the `/config/packages/doctrine.yaml` file, and config the `drive` to use the one you prefer, In our case is `pdo_sqlite`. You should have something like this:

```bash
doctrine:
    dbal:
        # driver: 'pdo_pgsql' # You can also choose to use the PostgreSQL
        driver: 'pdo_sqlite'
        url: '%env(DATABASE_URL)%'
```

2. Now, if you are using the PHP first time, you should activate the database drivers, to do it, go to your php files in (Windows) and open the `php.ini` file: `C:\php\php.ini`.
3. Once you are in `C:\php\php.ini`, open it with your favorite text editor and then de-coment the extensions of the db driver you want to use. for example, `pdo_pgsql` and `pgsql` for PostgreSQL or `sqlite3` and `pdo_sqlite` for SQLite. Filally you should have something like this:

```bash
      ...
;extension=pdo_mysql
extension=pdo_pgsql
extension=pdo_sqlite
extension=pgsql
      ...
;extension=sodium
extension=sqlite3
      ...

``` 

### Create the DB

If the /var/data.db file does not exist, you can create the db file using the following commands:

```bash
php bin/console doctrine:database:create
php bin/console doctrine:schema:update --force 
```

<details><summary><strong>👀 In case of: $${\color{red}SQLSTATE[HY000] [14] unable to open database file}$$ error.</strong></summary>

- If:  `An exception occurred in the driver: SQLSTATE[HY000] [14] unable to open database file` appears, in this case, if you want to run a local server, the best way is to provide an absolute path to the db file. First go to your /var folder, open a terminal and run `pwd`, then go and edit the `DATABASE_URL` in the `.env` file, for example you should have something like: `DATABASE_URL="<YOUR_DB_DRIVER>:///C:/<PATH_TO_PROJECT>/symfony_crud_poc/var/data.db"`. Finally re-run the database:create code.
    
</details>




Now we make our first migrations to the database, there may be no changes.

```bash
php bin/console doctrine:migrations:diff
php bin/console doctrine:migrations:migrate
```

## Run the server

```bash
symfony server:start
```

# 🚀 Testing the API Endpoints

<!-- <details>
<summary><strong>👉 Using Swagger UI</strong></summary> -->

## 👉 Using Swagger UI

If you are running the server locally, you can access the Swagger UI at [http://localhost:8000/api-docs](http://localhost:8000/api-docs). Once deployed, it will be available at `https://<your-domain>/api-docs`.

<!--
</details>

<details><summary><strong>🔥 Using Next.js Frontend Locally 🔥</strong></summary> -->

<hr/>

## 🔥 Using Next.js Frontend Locally 🔥

This server is meant to be used as a local development server for the frontend application with the Symfony API server running on port `8000`, you can change the port in the `.env.local` file.

1. Download the project locally. You can go to the [frontend repository](https://github.com/SrVladyslav/symfony_crud_poc_frontend) and clone it to your local machine. Or just run:

```bash
git clone https://github.com/SrVladyslav/symfony_crud_poc_frontend.git
```

2. Navigate to the project directory and install the required dependencies using npm:

```bash
cd frontend
npm install
```

3. Start the development server and open your browser to `http://localhost:3000/`:

```bash
npm run dev
```

4. Now you can try out the API endpoints, by default it will try to connect to the local server running on port `http://localhost:8000`, but you can change this URL using the input on TOP and clicking the `Change URL` button.

<!-- </details> -->

<hr/>

<details>
<summary><strong>Using Postman</strong></summary>

### 1. Download and Install Postman

1. Visit the [Postman Official Site](https://www.postman.com/downloads/).
2. Choose the version suitable for your operating system (Windows, macOS, or Linux).
3. Download and run the installer.
4. Follow the installation instructions to complete the setup.

### 2. Create a New Request in Postman

1. **Open Postman.**
2. **Create a New Request:**

   - Click on the "New" button in the upper-left corner.
   - Select "Request" from the dropdown menu.
   - Enter a name for your request (e.g., "Get All Categories").
   - Optionally, save it to a collection for organization.

3. **Set the Request Method and URL:**
   - Choose the HTTP method (GET, POST, PUT, DELETE) from the dropdown menu next to the URL field.
   - Enter the API endpoint URL (e.g., `https://api.example.com/api/categories/get`).

### 3. Add Headers

1. **Go to the Headers Tab:**

   - Click on the "Headers" tab below the URL field.

2. **Add Authorization Header:**

   - Click on the "Key" field and type `Authorization`.
   - Click on the "Value" field and enter `Bearer YOUR_API_TOKEN`, replacing `YOUR_API_TOKEN` with your actual API token.

   Example:

   Key: `Authorization`
   Value: `Bearer mY-Very-Secret-Token`

### 4. Send the Request and Review the Response

1. **Send the Request:**

- Click the "Send" button to submit your request.

2. **Review the Response:**

- Postman will display the response in the lower part of the window.
- You can view the status code, response time, and headers.
- The response body will be shown in the "Body" tab. You can switch between different formats like JSON, HTML, or raw text.

Example Response:

```json
{
    "status": "success",
    "message": "Found successfully",
    "page": "x",
    "limit": "y",
    "totalPages": "z",
    "prevPage": "/api/categories/get?page=x-1",
    "nextPage": "/api/categories/get?page=x+1",
    "data": [
        {
            "id": 1,
            "name": "category 1",
            "description": "Some category description",
            "products": [
                {
                    "id": 8,
                    "name": "Product X",
                    "description": "product description Y",
                    "price": 0.75,
                    "category": 1
                }
            ]
        },
        ...
    ]
}
```

</details>

<details><summary><strong>Using Next.js Frontend deployed on Vercel </strong></summary>

This is a deployed version of the [frontend repository](https://github.com/SrVladyslav/symfony_crud_poc_frontend) on Vercel which is connected to the Symfony API server running on `platform.sh`.

To start using it just click [here](https://symfony.vlamaz.com/).

</details>

## 🗺 Endpoints

### Category Endpoints

| Endpoint                      | Method | Description                                                               |
| ----------------------------- | ------ | ------------------------------------------------------------------------- |
| `/api/categories/get`         | GET    | Retrieves a list of all categories.                                       |
| `/api/categories/{id}/get`    | GET    | Retrieves a specific category by its unique `id`.                         |
| `/api/categories/create`      | POST   | Creates a new category with the provided details.                         |
| `/api/categories/{id}/update` | PUT    | Updates the category identified by the given `id` with the provided data. |
| `/api/categories/{id}/delete` | DELETE | Deletes the category identified by the given `id`.                        |

### Products Endpoints

| Endpoint                    | Method | Description                                                          |
| --------------------------- | ------ | -------------------------------------------------------------------- |
| `/api/products/get`         | GET    | Retrieves a list of all products.                                    |
| `/api/products/{id}/get`    | GET    | Retrieves a specific product by its unique `id`.                     |
| `/api/products/create`      | POST   | Creates a new product with the provided details.                     |
| `/api/products/{id}/update` | PUT    | Updates the product with the specified `id` using the provided data. |
| `/api/products/{id}/delete` | DELETE | Deletes the product identified by the given `id`.                    |

<hr>

### Useful Commands

- **`symfony server:start`**

  Starts the Symfony local development server. This command is useful for running your Symfony application locally and testing it in a development environment.

- **`php bin/console doctrine:migrations:diff`**

  Generates a new migration file based on changes in your Doctrine entities. This is used to create migration scripts that will update your database schema according to the changes made in your entity classes.

- **`php bin/console doctrine:migrations:migrate`**

  Applies the migrations to the database. Use this command to execute migration scripts and update your database schema to match your entities.

- **`php bin/console make:migration`**

  Creates a new empty migration class. This is useful if you need to manually write custom migration logic instead of relying on `doctrine:migrations:diff` to generate migrations automatically.

- **`composer require nelmio/cors-bundle`**
- **`php bin/console debug:router`**
- **`composer require nelmio/api-doc-bundle`**
- **`composer require asset`**
