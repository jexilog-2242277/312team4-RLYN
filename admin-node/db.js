const { Pool } = require('pg');

const pool = new Pool({
  user: 'postgres',
  host: 'localhost',
  database: '312team4-RLYN',
  password: '123',
  port: 5432,
});

module.exports = pool;
