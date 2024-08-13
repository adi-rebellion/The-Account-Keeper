# The Account Keeper

# Project Description

This project is built using **Laravel 10** and utilizes **Docker** for seamless deployment. The application leverages **Laravel Passport** for secure token-based authentication, ensuring a robust and scalable authentication mechanism.

## Features

The project includes a comprehensive set of APIs for managing financial transactions, including:

1. **Transaction Management**: 
   - APIs to create and manage debit and credit transactions, ensuring validation for insufficient balances.
   
2. **Daily Closing Balance**: 
   - Retrieve the closing balance for the past specified days.
   
3. **Average Balance Calculation**: 
   - Calculate average balances over defined time segments.
   
4. **Income Calculations**: 
   - APIs to compute total income over specified days and amounts, along with transaction counts.
   
5. **Transaction Filtering**: 
   - Options to filter transactions based on various parameters, such as category IDs and minimum amounts.

All API endpoints are designed to be intuitive and provide detailed responses for ease of integration and usage.


## Requirements

- Laravel 10.10
- Docker for deployment (`sudo docker compose build` and `sudo docker compose up`)
- PHP runs on port 39100, PHPMyAdmin on port 29182

## Installation and Setup

1. Clone the repository:

   ```bash
   git clone https://github.com/adi-rebellion/The-Account-Keeper.git
   cd TaskKeeper
   ```

2. Build and start Docker containers:

   ```bash
   sudo docker compose build
   sudo docker compose up
   ```

3. Access the application in your web browser:

   ```
   http://localhost:39100
   ```






## API Documentation

📘 API documentation is available in Swagger format at:

Explore the API using Swagger UI with Bearer authorization.

1. Click on the `Authorize` button.
2. Enter `Bearer your_access_token` in the `Value` field.
3. Click `Authorize`.
4. Now you can explore and test API endpoints securely.


```
http://localhost:39100/api/documentation
```

📘 API documentation is available in Postman format at:

 
1. Click on the `Import` button.
2. Enter `URL` in the `Value` field.
3. Click `Import`.
4. Now you can explore and test API endpoints securely.


```
https://documenter.getpostman.com/view/13214997/2sA3s4nWCp
```



### Authentication

#### User Register
- Endpoint: `/api/register`
- Method: POST
- Parameters:
  - `name` (string): User's name
  - `email` (string): User's email address
  - `password` (string): User's password
  - `confirm_password` (string): User's confirm password
- Response:
  - `message`: User registered successfully. 
  - `access_token`: Token for accessing protected endpoints

#### User Login
- Endpoint: `/api/login`
- Method: POST
- Parameters:
  - `email` (string): User's email address
  - `password` (string): User's password
- Response:
  - `message`: User logged in successfully. 
  - `access_token`: Token for accessing protected endpoints

#### User Logout
- Endpoint: `/api/logout`
- Method: POST
- Headers:
  - `Authorization: Bearer {access_token}`
- Response:
  - `message`: User logged out successfully. 


## Contributors

- Aditya Naidu

