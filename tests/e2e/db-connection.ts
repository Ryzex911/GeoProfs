import mysql from 'mysql2/promise';

export async function dbConnection() {
    try {
        const connection = await mysql.createConnection({
            host: '127.0.0.1',
            user: 'sail',
            password: 'password',
            database: 'laravel',
        });
        console.log('Connected with db');
        return connection;
    } catch (error) {
        console.error('Connection error:', error);
        throw error;
    }
}
