const express = require('express');
const router = express.Router();
const pool = require('../db');
const adminOnly = require('../middleware/adminOnly');

// Show login page
router.get('/login', (req, res) => {
    res.render('login', { error: null });
});

// Handle login
router.post('/login', async (req, res) => {
    const { email, password } = req.body;

    try {
        const result = await pool.query(
            "SELECT * FROM users WHERE email = $1 AND role = 'admin'",
            [email]
        );

        if (result.rows.length === 0 || result.rows[0].password !== password) {
            return res.render('login', { error: 'Invalid email or password' });
        }

        const admin = result.rows[0];
        req.session.admin = {
            id: admin.user_id,
            email: admin.email,
            name: admin.name,
            role: admin.role
        };

        // Redirect to the users page
        res.redirect('/admin/users');

    } catch (err) {
        console.error(err);
        res.render('login', { error: 'Server error' });
    }
});

// Logout
router.get('/logout', (req, res) => {
    req.session.destroy(() => {
        res.redirect('/admin/login');
    });
});

module.exports = router;
