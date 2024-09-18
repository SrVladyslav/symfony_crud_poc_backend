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
cd symfony_crud_poc_backend
composer install
```

3. Go to the `.env` file in the project root directory and change the `API_TOKEN` in case you want to use your own one:

```bash
API_TOKEN=mY-Very-Secret-Token
```

## Database configuration

First let's set permissions for the var directory

```bash
chmod -R 755 var
chmod -R 777 var
```

### Prior configurations

Before creating the Database, you should choose the DB to use, so in our case will be the SQLite, but you can use other ones if you want. 

1. [GO TO STEP 2 IF WANT TO USE DEFAULT CONFIG] Let's config the doctrine yaml file, in your project open the `/config/packages/doctrine.yaml` file, and config the `drive` to use the one you prefer, In our case is `pdo_sqlite`. You should have something like this:

```bash
doctrine:
    dbal:
        # driver: 'pdo_pgsql' # You can also choose to use the PostgreSQL
        driver: 'pdo_sqlite'
        url: '%env(DATABASE_URL)%'
```

2. Now, $${\color{yellow}if \space this \space is \space the \space first \space time \space you \space are \space using \space the \space PHP}$$, you should activate the database drivers, to do it, go to your php files, once you are in (Windows) `C:\php\php.ini`, open the file with your favorite text editor and then de-coment the extensions of the db drivers you want to use. for example, `pdo_pgsql` and `pgsql` for PostgreSQL or `sqlite3` and `pdo_sqlite` for SQLite. Filally you should have something like this:

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

3. Once you decided the driver you will be using, go to the `.env` file and change the `DATABASE_URL` to one you want to use.  $${\color{yellow}By \space default \space is \space set \space to \space local \space SQLite}$$, so if you will be running it in local with SQLite, don't touch it.

```bash
DATABASE_URL="sqlite:///%kernel.project_dir%/var/data.db" # Default configuration
DATABASE_URL="<YOUR_DRIVER>:///<PATH_TO_PROJECT_VAR>/var/data.db"
```

You can also try [Supabase](https://supabase.com/) which is using `PostgreSQL`.

### Create the DB

If the /var/data.db file does not exist, you can create the db file using the following commands:

```bash
php bin/console doctrine:database:create
```

<details><summary><strong>👀 In case of: $${\color{red}SQLSTATE[HY000] \space [14] \space unable \space to \space open \space database \space file}$$ error.</strong></summary>

- If:  `An exception occurred in the driver: SQLSTATE[HY000] [14] unable to open database file` appears, in this case, if you want to run a local server, the best way is to provide an absolute path to the db file. First go to your /var folder, open the terminal, run `pwd` and copy the PATH, then go and edit the `DATABASE_URL` in the `.env` file, for example you should have something like: `DATABASE_URL="<YOUR_DB_DRIVER>:///C:/<PATH_FROM_PWD>/data.db"`. Finally re-run the database:create code.
    
</details>

Now make the migrations files by running:

```bash
php bin/console doctrine:migrations:diff
```

Push the migrations to the DB by running:

```bash
php bin/console doctrine:migrations:migrate
```

Type `yes` when WARNING is prompted.

## Run the server

```bash
symfony server:start
```

# 🚀 Testing the API Endpoints

<!-- <details>
<summary><strong>👉 Using Swagger UI</strong></summary> -->

## 👉 Using Swagger UI

If you are running the server locally, you can access the Swagger UI at [http://localhost:8000/api-docs](http://localhost:8000/api-docs). Once the server is deployed, it will be available at `https://<your-domain>/api-docs`. Remember that we are using AUTH Token, set the Bearer by clicking `Authorize` before using the Swagger UI.

<!--
</details>

<details><summary><strong>🔥 Using Dedicated Next.js Frontend Locally 🔥</strong></summary> -->

<hr/>

## 🔥 Using Next.js Frontend Locally 🔥

![Dedicated NextJS Frontend](https://github.com/SrVladyslav/symfony_crud_poc_frontend/blob/main/public/images/frontend_view.png?raw=true)

This is a frontend application created for testing purposes of the Symfony API server running on localhost and port `8000`.

NOTE: If you deploy the Backend to prod, you also have this frontend server deployed to vercel [here](https://symfony.vlamaz.com/).

1. Download the project locally. You can go to the [frontend repository](https://github.com/SrVladyslav/symfony_crud_poc_frontend) and clone it to your local machine. Or just run:

```bash
git clone https://github.com/SrVladyslav/symfony_crud_poc_frontend.git
```

2. Navigate to the project directory and install the required dependencies using npm:

```bash
cd symfony_crud_poc_frontend
npm install
```

3. Start the development server or create a build, which has better performance. Finally, open your browser on [`http://localhost:3000/`](http://localhost:3000/):

```bash
npm run dev
```

or

```bash
npm run build
npm run start
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

- **`php bin/console doctrine:schema:update --force`**

  Updates your database schema based on your Doctrine entity mappings.

Some of the used migrations.

- **`composer require nelmio/cors-bundle`**
- **`composer require nelmio/api-doc-bundle`**
- **`composer require asset`**
