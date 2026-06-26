# AI Manager

AI Manager is a modern engineering management dashboard designed to streamline team operations, track project progress, and leverage artificial intelligence to provide actionable leadership insights. By integrating directly with developer tools like GitLab, AI Manager automates repetitive managerial tasks, logs attendance, manages meetings, and uses LLMs to generate comprehensive performance reports and meeting transcripts, helping engineering managers make data-driven decisions.

## Features

- **Employee & Team Management**: Organize developers into structured teams, manage profiles, and track reporting lines.
- **Project & Task Tracking**: Assign tasks, monitor progress status, and manage active project lifecycles.
- **Attendance Tracking**: Log daily attendance and monitor team availability.
- **Meeting Management**: Schedule team meetings with support for recordings and automated transcriptions.
- **AI Chat Assistant**: Interactive AI assistant powered by NVIDIA LLM to query team data and generate summaries.
- **AI-Powered Report Generation**: Automated summary reports and commit-log analysis.
- **Leadership Insights**: High-level assessments of team velocity, bottlenecks, and overall health.
- **GitLab Integration**: Automatically synchronize repository commits and analyze code contributions.
- **Analytics Dashboard**: Visual representations of active projects, task completion rates, and team metrics.
- **CSV Import/Export**: Easy bulk data import and export for employee and project records.
- **Authentication System**: Secure access control using standard Laravel authentication and API token management.

## Tech Stack

### Backend

- **Framework**: Laravel 12
- **Language**: PHP 8.x
- **Core Architecture**: Custom services for AI chat, transcription, and GitLab synchronization

### Frontend

- **Views**: Blade template engine
- **Styling**: Bootstrap 5 & Bootstrap Icons
- **Interactions**: Vanilla JavaScript

### Database

- **Development**: MySQL
- **Deployment**: SQLite

### Integrations

- **AI Orchestration**: NVIDIA LLM API
- **Version Control**: GitLab API
- **Hosting / Deployment**: Render

## Screenshots

### Dashboard

_(Add screenshot here)_

### Reports

_(Add screenshot here)_

### Leadership Insights

_(Add screenshot here)_

### AI Assistant

_(Add screenshot here)_

## Installation

Follow these steps to set up the project locally:

1. **Clone the repository**:

    ```bash
    git clone https://github.com/your-username/manager-agent.git
    cd manager-agent
    ```

2. **Install dependencies**:

    ```bash
    composer install
    npm install && npm run build
    ```

3. **Configure environment variables**:
   Create a `.env` file from the example template:

    ```bash
    cp .env.example .env
    ```

    Open the `.env` file and set up your database connection, NVIDIA LLM API credentials, and GitLab integration settings:

    ```env
    DB_CONNECTION=mysql
    DB_DATABASE=manager_agent

    NVIDIA_API_KEY=your_nvidia_api_key
    GITLAB_PERSONAL_ACCESS_TOKEN=your_gitlab_token
    ```

4. **Generate application key**:

    ```bash
    php artisan key:generate
    ```

5. **Run database migrations**:

    ```bash
    php artisan migrate --seed
    ```

6. **Start the development server**:
    ```bash
    php artisan serve
    ```

## Future Improvements

- **Real-time notifications**: Implement WebSockets or push notifications for instant task updates.
- **Advanced analytics**: Interactive burn-down charts, velocity metrics, and team capacity planning.
- **Additional AI capabilities**: Multi-modal meeting summarization and auto-assignment of tasks based on developer expertise.
- **Role-based access control (RBAC)**: Fine-grained user roles (e.g., Admin, Manager, Developer) with distinct permission scopes.
- **Enhanced GitLab insights**: Pull request review cycle analytics, code review velocity, and quality tracking.

## License

This project is licensed under the MIT License.
