# BuildForge AI - Production-Ready AI SaaS Platform

BuildForge AI is an automated platform that converts business ideas or website URLs into complete, production-ready software systems.

## Features
- **AI Idea Analyzer**: Extracts core features, entities, and workflows from ideas.
- **Architecture Generator**: Designs scalable system architectures.
- **Database Schema Generator**: Generates PostgreSQL schemas and Laravel migrations.
- **API Generator**: Automatically creates REST APIs with Laravel 11.
- **Frontend Generator**: Builds responsive Next.js dashboards with ShadCN UI.
- **Stripe Integration**: Multi-tier subscription billing.
- **Docker & Kubernetes Ready**: Scalable infrastructure out of the box.

## Tech Stack
- **Frontend**: Next.js 14+, TypeScript, TailwindCSS, ShadCN UI, React Query, Zustand.
- **Backend**: Laravel 11 API, PostgreSQL, Redis, Laravel Sanctum, Spatie Permissions.
- **AI**: OpenAI GPT-4 API.
- **Infra**: Docker, Kubernetes.

## Getting Started

### Prerequisites
- Docker & Docker Compose
- Node.js & NPM
- PHP 8.2+ & Composer

### Installation
1. Clone the repository.
2. Setup environment variables:
   ```bash
   cp backend/.env.example backend/.env
   # Add your OPENAI_API_KEY and STRIPE keys to backend/.env
   ```
3. Run the platform with Docker Compose:
   ```bash
   docker-compose up -d --build
   ```
4. Access the platform:
   - Frontend: [http://localhost:3000](http://localhost:3000)
   - Backend API: [http://localhost:8000](http://localhost:8000)

## Deployment
The platform includes Kubernetes manifests in `k8s-manifests.yaml` for production deployment on AWS, DigitalOcean, or other providers.

## License
MIT
