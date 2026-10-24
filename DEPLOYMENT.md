# Deploying Zonita TV on Render.com

This guide provides step-by-step instructions for deploying **Zonita TV** on [Render](https://render.com).

---

## Method 1: 1-Click Blueprint Deploy (Recommended)

Because the project includes [`render.yaml`](./render.yaml) and a production [`Dockerfile`](./Dockerfile), Render can automatically configure the service for you.

1. **Sign in to Render**:
   - Go to [dashboard.render.com](https://dashboard.render.com) and log in with your GitHub account (`Benniechat`).

2. **Create New Blueprint Instance**:
   - Click **New +** in the top navigation bar and select **Blueprint**.
   - Connect your GitHub repository: `Benniechat/ZonitaTV`.
   - Render will detect the `render.yaml` configuration automatically.

3. **Deploy**:
   - Give the instance a name (e.g. `zonitatv`).
   - Click **Apply**.
   - Render will build the Docker container (compiling Vite assets and setting up PHP 8.3 + Nginx) and boot your application on your custom Render subdomain (`https://zonitatv.onrender.com`).

---

## Method 2: Manual Web Service Setup (Step-by-Step)

If you prefer to configure the Web Service manually via Render's dashboard:

1. Click **New +** > **Web Service**.
2. Select **Build and deploy from a Git repository**.
3. Choose your repository: `Benniechat/ZonitaTV`.
4. Configure the service settings:
   - **Name**: `zonitatv`
   - **Region**: Choose the closest region to your primary audience (e.g., Frankfurt or Oregon).
   - **Runtime**: Select **Docker**.
   - **Instance Type**: **Free** (or Starter for persistent disks / high traffic).
5. **Environment Variables**:
   Under **Environment Variables**, add the following:

   | Key | Value | Description |
   |---|---|---|
   | `APP_NAME` | `Zonita TV` | Application Name |
   | `APP_ENV` | `production` | Production Environment |
   | `APP_DEBUG` | `false` | Disable debug backtraces |
   | `APP_KEY` | *(Generate in Render or run `php artisan key:generate --show`)* | 32-character encryption key |
   | `APP_URL` | `https://zonitatv.onrender.com` | Your Render URL |
   | `DB_CONNECTION` | `sqlite` | Database engine |
   | `DB_DATABASE` | `/var/www/html/database/database.sqlite` | SQLite database file path |
   | `SESSION_DRIVER` | `cookie` | Stateful cookie sessions |
   | `QUEUE_CONNECTION` | `sync` | Playout engine queue mode |
   | `CACHE_STORE` | `file` | Cache driver |

6. Click **Create Web Service**.

---

## Using Supabase as the Database (Recommended for Production)

**Yes, absolutely!** Supabase provides a cloud-hosted, high-performance PostgreSQL database that works natively with Laravel. It solves Render's ephemeral disk limitation completely for free.

### 1. Retrieve Your Supabase Database Credentials
1. Go to your [Supabase Dashboard](https://supabase.com/dashboard) and open your project.
2. Navigate to **Project Settings** (gear icon) &rarr; **Database**.
3. Under **Connection string**, select **URI** or **Parameters**.
   - Note: Choose **Session mode (port 5432)** or direct connection (port 5432).

### 2. Configure Environment Variables in Render
In your Render Web Service dashboard, set:

#### Option A: Individual Variables
| Key | Value | Example |
|---|---|---|
| `DB_CONNECTION` | `pgsql` | `pgsql` |
| `DB_HOST` | `aws-0-[region].pooler.supabase.com` | `aws-0-eu-central-1.pooler.supabase.com` |
| `DB_PORT` | `5432` | `5432` |
| `DB_DATABASE` | `postgres` | `postgres` |
| `DB_USERNAME` | `postgres.[project-ref]` | `postgres.abcdefghijklm` |
| `DB_PASSWORD` | *(Your Supabase DB Password)* | `YourSecretPassword123!` |
| `DB_SSLMODE` | `require` | `require` |

#### Option B: Single Connection URL
Alternatively, simply set:
- **`DB_CONNECTION`**: `pgsql`
- **`DATABASE_URL`**: `postgresql://postgres.[project-ref]:[password]@aws-0-[region].pooler.supabase.com:5432/postgres?sslmode=require`

### 3. Automatic Schema & Data Seeding
When your Render container starts, the startup script automatically:
1. Connects to Supabase over SSL (`pdo_pgsql`).
2. Runs all database migrations (`php artisan migrate --force`).
3. If the database is fresh, automatically seeds the 50 channels and 5 Zonita 24/7 virtual broadcasting stations with their 7-day rolling schedules!

---

## Database Persistence

- **Using SQLite (Default)**:
  - On first boot, the Docker container automatically creates the SQLite database and populates it with your initial channel catalogue from `database/zonita_master_schema_and_data.sql`.
  - For long-term persistent storage on Render across deploys, you can attach a Render **Disk** (e.g. 1GB mounted at `/var/www/html/database`) in the Render Service settings.

- **Using Render Managed PostgreSQL (Optional)**:
  - Render offers a free managed PostgreSQL database.
  - If you create a Render Postgres instance, simply change:
    - `DB_CONNECTION`: `pgsql`
    - `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`: Set from your Render Postgres Internal Database URL.

---

## Verifying Deployment

Once the build finishes:
1. Open your assigned URL: `https://zonitatv.onrender.com`.
2. Check the live stations (e.g. `https://zonitatv.onrender.com/channel/zonita-football-24-7`).
3. Access the operations console at `https://zonitatv.onrender.com/admin/broadcasting`.
