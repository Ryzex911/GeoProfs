import mysql from "mysql2/promise";

async function testConnection() {
    try {
        const connection = await mysql.createConnection({
            host: '127.0.0.1',
            user: 'sail',
            password: 'password',
            database: 'laravel',
        })
        console.log('connected with db')

        const [rows] = await connection.query('SELECT * FROM password_reset_tokens');
        console.log('DB tables', rows);

        await connection.end();
    }catch (error){
        console.error('connection error', error)
    }
}

testConnection();
