This document provides an overview of the available API endpoints, their usage, and guidelines on how to test them.

# 📚 Table of contents 

- [API Important considerations](https://github.com/SrVladyslav/symfony_crud_poc_backend?tab=readme-ov-file#%EF%B8%8F-api-important-considerations)
- [Project setup](https://github.com/SrVladyslav/symfony_crud_poc_backend?tab=readme-ov-file#-project-setup)
- [Testing the API Endpoints](https://github.com/SrVladyslav/symfony_crud_poc_backend?tab=readme-ov-file#-testing-the-api-endpoints)
- [Symfony deployment to Platform.sh](https://github.com/SrVladyslav/symfony_crud_poc_backend?tab=readme-ov-file#-symfony-deployment-to-platformsh)
- [Test the deployed backend endpoints](https://github.com/SrVladyslav/symfony_crud_poc_backend?tab=readme-ov-file#-test-the-deployed-backend-endpoints)
- [Useful Commands](https://github.com/SrVladyslav/symfony_crud_poc_backend?tab=readme-ov-file#useful-commands)

# ⚠️ API Important considerations

- We suppose that each product must have a `category`, so $${\color{yellow}first \space you \space need \space to \space create \space a \space category \space before \space creating \space a \space product}$$.

- The Many-To-One relation between `Category` and `Product` is implemented with `ON DELETE CASCADE`, so be carefull when deleting a category, $${\color{yellow}all \space the \space products \space associated \space with \space it \space will \space be \space deleted \space as \space well}$$.

- All endpoints require a valid authentication token. Include the token in the `Authorization` header as follows:

```bash

Authorization: Bearer mY-Very-Secret-Token
```

<br/>

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

## 1. Clone the Symfony project locally

1.1. Clone the repository to your local machine using the following command:

```bash
git clone https://github.com/SrVladyslav/symfony_crud_poc_backend.git
```

1.2. Navigate to the project directory and install the required dependencies using Composer:

```bash
cd symfony_crud_poc_backend
composer install
```

1.3. 🔑[OPTIONAL IF RUNNING LOCALLY]: For the sake of simplicity, we use the `.env` file in the project root directory to store the API AUTH Token in the `API_TOKEN` variable. Change the `API_TOKEN` in case you want to use your own one:

```bash
API_TOKEN=mY-Very-Secret-Token
```

## 2. Database configuration

### 2.1. Prior configurations

2.1.1 By default this project is configured to use SQLite database for local PoC.

⚠️ $${\color{yellow}If \space this \space is \space the \space first \space time \space you \space are \space using \space PHP}$$ ⚠️, you should activate the database drivers:
   - Locate your `php.ini` File: on Windows is located here `C:\php\php.ini`.
   - Edit the file using your preferred text editor and uncomment the required extensions by removing the leading `;`. For example, `pdo_pgsql` and `pgsql` for PostgreSQL or `sqlite3` and `pdo_sqlite` for SQLite (Default). After saving the file, your configuration should now include the necessary drivers, like this:

```bash
      ...
;extension=pdo_pgsql
extension=pdo_sqlite
      ...
extension=sqlite3
      ...
```

<details><summary><strong> $${\color{yellow}[OPTIONAL]: \space In \space case \space you \space change \space the \space default \space SQLite \space DB, \space do \space this:}$$</strong></summary>
    
Let's config the doctrine yaml file, in your project open the `/config/packages/doctrine.yaml` file, and edit the `driver` to use the one you prefer. We use `pdo_sqlite` by default. Remember that in order to use the driver, you should activate it first in `C:\php\php.ini` file. 

```bash
doctrine:
    dbal:
        # driver: 'pdo_pgsql' # You can also choose to use the PostgreSQL
        driver: 'pdo_sqlite'
        url: '%env(DATABASE_URL)%'
```

2.1.2. Once you decided the driver you will be using, go to the `.env` file and change the `DATABASE_URL` to one you want to use.

```bash
DATABASE_URL="sqlite:///%kernel.project_dir%/var/data.db" # Default configuration
DATABASE_URL="<YOUR_DRIVER>:///<PATH_TO_PROJECT_VAR>/var/data.db"
```

</details>

### 2.2. Create the DB

2.2.1. If the /var/data.db file does not exist, you can create the db file using the following command:

```bash
php bin/console doctrine:database:create
```

<details><summary><strong>👀 In case of: $${\color{red}SQLSTATE[HY000] \space [14] \space unable \space to \space open \space database \space file}$$ error.</strong></summary>

- If: `An exception occurred in the driver: SQLSTATE[HY000] [14] unable to open database file` appears, in this case, if you want to run a local server, the best way is to provide an absolute path to the db file. First go to your /var folder, open the terminal, run `pwd` and copy the PATH, then go and edit the `DATABASE_URL` in the `.env` file, for example you should have something like: `DATABASE_URL="<YOUR_DB_DRIVER>:///C:/<PATH_FROM_PWD>/data.db"`. Finally re-run the database:create code.

</details>

<details><summary><strong>[OPTIONAL]: Migrate the database if you modify its tables</strong></summary>

2.2.1. When making migrations, you need to check the migration code before migrating to DB (`/migrations/<migration>.php`) File, sometimes the generated code is not correct.
On the other hand, we were using SQLite in local, but in prod you should use something else, in this case, we used PostgreSQL.
So, we need to check the code and change it to match the DB, for example take a look at the migration code:

```php
public function up(Schema $schema): void
{
    // Detect if we are using PostgreSQL
    if ($this->connection->getDatabasePlatform()->getName() === 'postgresql') {
        $this->addSql('CREATE TABLE category (
            id SERIAL PRIMARY KEY,
            name VARCHAR(128) NOT NULL,
            description TEXT DEFAULT NULL
        )');
        $this->addSql('CREATE TABLE product (
            id SERIAL PRIMARY KEY,
            category_id INTEGER DEFAULT NULL,
            name VARCHAR(128) NOT NULL,
            description TEXT DEFAULT NULL,
            price DOUBLE PRECISION NOT NULL,
            CONSTRAINT FK_D34A04AD12469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE
        )');
        $this->addSql('CREATE INDEX IDX_D34A04AD12469DE2 ON product (category_id)');
    }
    // Detect if we are using SQLite
    elseif ($this->connection->getDatabasePlatform()->getName() === 'sqlite') {
        $this->addSql('CREATE TABLE category (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(128) NOT NULL,
            description TEXT DEFAULT NULL
        )');
        $this->addSql('CREATE TABLE product (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            category_id INTEGER DEFAULT NULL,
            name VARCHAR(128) NOT NULL,
            description TEXT DEFAULT NULL,
            price DOUBLE PRECISION NOT NULL,
            CONSTRAINT FK_D34A04AD12469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE
        )');
        $this->addSql('CREATE INDEX IDX_D34A04AD12469DE2 ON product (category_id)');
    }
}
```

You can see that we are using `SERIAL` for the `id` column in PostgreSQ, but in SQLite we are using `INTEGER` as well as `AUTOINCREMENT`, so we need to change the code to match the DB.

2.2. To make migrations to the table, run:

```bash
php bin/console doctrine:migrations:diff
```

</details>

> [!TIP]  
> Always check the new migration file in the `/migrations` folder after creating new onew, sometimes the generator produces bad SQL code.

2.2.2. Migrate the migrations to the DB by running:
If you're using the default configuration, 

```bash
php bin/console doctrine:migrations:migrate
```

Type `yes` when WARNING is prompted.

## 3. Run the server

```bash
symfony server:start
```

🎉Congratulations!🎉: Now you are running the Symfony server locally.

<br/>

# 🚀 Testing the API Endpoints

## 1. 👉 Using Swagger UI

If you are running the server locally, you can access the Swagger UI at [http://localhost:8000/api-docs](http://localhost:8000/api-docs). Once the server is deployed, it will be available at `https://<your-domain>/api-docs`. Remember that we are using AUTH Token, set the Bearer by clicking `Authorize` before using the Swagger UI.

## 2. 🔥 Using Next.js Frontend Locally 🔥

![Dedicated NextJS Frontend](https://github.com/SrVladyslav/symfony_crud_poc_frontend/blob/main/public/images/localhostfrontend.png?raw=true)

This is a frontend app created for testing purposes of the Symfony API server running on localhost at port `8000`. It allows you to add a `page limit` query parameter as well as `page` parameter for pagination.

2.1. Keep the Symfony server running and open another terminal in the directory where you want to save this project, then download the it locally. You can go to the [frontend repository](https://github.com/SrVladyslav/symfony_crud_poc_frontend) and clone it to your local machine. Or just run:

```bash
git clone https://github.com/SrVladyslav/symfony_crud_poc_frontend.git
```

2.2. Navigate to the project directory and install the required dependencies using npm:

```bash
cd symfony_crud_poc_frontend
npm install
```

2.3. Start the development server or create a build, which has better performance:

```bash
npm run dev
```

or

```bash
npm run build
npm run start
```

🎉Congratulations!🎉: Now you are running your frontend server.

2.4. Finally, open your browser on [`http://localhost:3000/`](http://localhost:3000/) and start using the endpoints.

> [!NOTE]  
> If you have deployed your Backend to PROD, you can also use this frontend deployed to Vercel on [https://symfony.vlamaz.com/](https://symfony.vlamaz.com/), all you need to do is input the new PROD url and click `Change URL` button.

## 3. More options

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

## 4. 🗺 Available endpoints

<details>
<summary><strong>4.1 Category Endpoints</strong></summary>

| Endpoint                      | Method | Description                                                               |
| ----------------------------- | ------ | ------------------------------------------------------------------------- |
| `/api/categories/get`         | GET    | Retrieves a list of all categories.                                       |
| `/api/categories/{id}/get`    | GET    | Retrieves a specific category by its unique `id`.                         |
| `/api/categories/create`      | POST   | Creates a new category with the provided details.                         |
| `/api/categories/{id}/update` | PUT    | Updates the category identified by the given `id` with the provided data. |
| `/api/categories/{id}/delete` | DELETE | Deletes the category identified by the given `id`.                        |
    
</details>

<details>
<summary><strong>4.2 Products Endpoints</strong></summary>

| Endpoint                    | Method | Description                                                          |
| --------------------------- | ------ | -------------------------------------------------------------------- |
| `/api/products/get`         | GET    | Retrieves a list of all products.                                    |
| `/api/products/{id}/get`    | GET    | Retrieves a specific product by its unique `id`.                     |
| `/api/products/create`      | POST   | Creates a new product with the provided details.                     |
| `/api/products/{id}/update` | PUT    | Updates the product with the specified `id` using the provided data. |
| `/api/products/{id}/delete` | DELETE | Deletes the product identified by the given `id`.                    |

</details>

<br/>

# 🔥 Symfony deployment to Platform.sh

You can also follow the [Official documentation](https://docs.platform.sh/guides/symfony.html) to get started with Symfony and Platform.sh.

## 1. Prerequisites

1.1. **Platform.sh Account**: [Sign up](https://auth.api.platform.sh/register?trial_type=general) for a Platform.sh account if you don’t already have one.
1.2. **Platform.sh CLI**: Install the Platform.sh CLI tool. Full instructions can be found [here](https://docs.platform.sh/administration/cli.html). Or if you are using Scoop, just run:

```bash
scoop bucket add platformsh https://github.com/platformsh/homebrew-tap.git
scoop install platform
```

## 2. Prepare Symfony Application

> [!NOTE]  
> This project already has all the Platform.sh configuration files.

2.1. Configure the `API_TOKEN`, `CORS ORIGIN` and the `APP_API_PRODUCTION_URL` in the `.env` file:

```bash
API_TOKEN=mY-Very-Secret-Token # Set some good token

# CORS_ALLOW_ORIGIN='<https://yourdomain.com>, <https://anotherdomain.com>'
# CORS_ALLOW_ORIGIN='^https?://(localhost|127\.0\.0\.1)(:[0-9]+)?$'
CORS_ALLOW_ORIGIN='*' # This is not good for production

# The URL of the API: CHANGE FOR PRODUCTION. Is user for Swagger server.
APP_API_PRODUCTION_URL=<YOUR_PRODUCTION_URL_HERE> # You can modify this once deployed the first time, so you will know your URL
```

## 3. Configure Platform.sh for Symfony

> [!IMPORTANT]  
> Delete the `.git` file before initializing your own, then init your Git Hub repository.
>
> ```bash
> git init
> git add .
> git commit -m "First commit"
> git branch -M main
> git remote add origin https://github.com/<YOUR_GIT_HUB_USER>/<PROJECT_NAME>.git
> git push -u origin main
> ```
> 

3.1. Log into Platform.sh: Follow the instructions to authenticate and connect your Platform.sh account.

```bash
platform login
```

3.2. Create a New Project, official guide [here](https://docs.platform.sh/get-started/deploy/init.html). If asks for `Default branch (--default-branch)`, set it to `true`.

```bash
platform project:create --title symfony_crud_poc --region eu-5.platform.sh
```

> [!TIP]
> In case you used the Platform.sh UI, you can link any repository to an existing Platform.sh project using the following command:
>
> ```bash
> symfony project:set-remote <PROJECT_ID>
>
> # or
> 
> platform project:set-remote <PROJECT_ID>
> ```

> [!CAUTION]
> When you create a new project on `Platform.sh` for the first time, a $${\color{red}1-Month \space Free \space Tier}$$ will also be activated. Please keep this in mind when starting your project.

3.2.1. You can check your project info running:

```bash
platform project:list
```

3.3. Commit changes and deploy to Platform.sh

```bash
git add .
git commit -m "Add Platform.sh configuration"
git push -u platform main
```

3.4. Now let's update the `APP_API_PRODUCTION_URL=<YOUR_PRODUCTION_URL_HERE>` variable in `.env`:
3.4.1. Go to the [Platform.sh console](https://console.platform.sh/) of your project.
3.4.2. Copy your project URL, check the image below:

<center>

![Platform project console](https://github.com/SrVladyslav/symfony_crud_poc_frontend/blob/main/public/images/platform_console.png?raw=true)

</center>

3.4.3. Now edit the `APP_API_PRODUCTION_URL=<YOUR_PRODUCTION_URL_HERE>` variable in `.env` and push the changes:

```bash
git add .
git commit -m "ENV changes"
git push -u platform main
```

🎉Congratulations!🎉: You are now running your API server.

<br/>

# 🚀 Test the deployed backend endpoints

![Frontend from vercel](https://github.com/SrVladyslav/symfony_crud_poc_frontend/blob/main/public/images/prod_frontend_new.png?raw=true)

- The easiest way is to use the `NextJS` frontend deployed to Vercel, just go to [https://symfony.vlamaz.com/](https://symfony.vlamaz.com/), paste your platform.sh project URL in the input, click `Change URL` button and reload the page. Voilà, now you can use your backend perfectly! You can also add a `page limit` query parameter as well as a specific `page` number for pagination.

- The other way is to use the backend Swagger, just go to `/api-docs`.

> [!TIP]
> You also have a deployed backend server on ( https://main-bvxea6i-hanqg6twaqie4.eu-5.platformsh.site/api-docs )

<hr/>
<br/>

# Useful Commands

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

- **`platform projects`**

  Lists your projects info, like projectID

- **`platform project:delete <project_id>`**

  Deletes the Platform.sh project by its ID

Some of the used bundles.

- **`composer require nelmio/cors-bundle`**
- **`composer require nelmio/api-doc-bundle`**
- **`composer require asset`**
- **`composer require symfony/orm-pack`**
- **`composer require symfony/maker-bundle`**
- **`composer require doctrine/doctrine-bundle`**
- **`composer require doctrine/dbal`**
