# Backend Setup Guide

## Prerequisites
- **PHP:** 8.5.4
- **Framework:** CakePHP 5
- **Database:** MySQL 8.0
- **Manager:** Composer

## Environment Setup
1. Copy the template from `be.env` in the root directory to `lopusboard-be/.env`.
2. Generate a 64-character secret salt for `SECURITY_SALT`:
   ```bash
   openssl rand -hex 32
   ```
3. Populate database credentials and Appwrite endpoint/project ID.

## Docker (Phase 2 Local Dev)
The root `docker-compose.yml` provides MySQL 8 and potentially Redis/Minio. 
- Build and start: `docker-compose up -d`.
- MySQL defaults: `lopusboard` (DB), `lopusboard` (User), `lopusboard` (Password).

## Initial Scaffolding
After setting up the database, run the migrations:
```bash
bin/cake migrations migrate
```
Generate initial code using Bake:
```bash
bin/cake bake all Users
bin/cake bake all Projects
bin/cake bake all ProjectMembers
bin/cake bake all Statuses
bin/cake bake all Departments
bin/cake bake all Issues
bin/cake bake all IssueRelations
bin/cake bake all Comments
bin/cake bake all WikiPages
bin/cake bake all WikiPageRevisions
bin/cake bake all WikiPageLinks
bin/cake bake all Attachments
bin/cake bake all AttachmentLinks
bin/cake bake all ActivityLog
```

## Running the API Server
```bash
bin/cake server -p 8080
```
Visit `http://localhost:8080/api/health` to verify.
