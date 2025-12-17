const express = require('express');
const router = express.Router();
const pool = require('../db');
const adminOnly = require('../middleware/adminOnly');

// ================= LIST USERS =================
router.get('/', adminOnly, async (req, res) => {
    const search = req.query.q || "";

    try {
        // Fetch all users
        const usersResult = await pool.query(
            `SELECT * FROM users
             WHERE name ILIKE $1 OR email ILIKE $1 OR role ILIKE $1
             ORDER BY user_id ASC`,
            [`%${search}%`]
        );

        // Fetch all organizations
        const orgsResult = await pool.query(
            `SELECT org_id, name FROM organizations ORDER BY org_id ASC`
        );

        console.log("Organizations fetched:", orgsResult.rows);

        res.render('users', {
            users: usersResult.rows,
            orgs: orgsResult.rows || [],
            search,
            admin: req.session.admin
        });

    } catch (err) {
        console.error(err);
        res.send("Error loading users");
    }
});

// ================= SHOW CREATE FORM =================
router.get('/create', adminOnly, async (req, res) => {
    try {
        const orgsResult = await pool.query(
            'SELECT org_id, name FROM organizations ORDER BY org_id ASC'
        );

        res.render('user_form', {
            user: null,
            formAction: '/admin/users/create',
            admin: req.session.admin,
            orgs: orgsResult.rows || []
        });
    } catch (err) {
        console.error(err);
        res.send("Error loading form");
    }
});

// ================= CREATE USER =================
router.post('/create', adminOnly, async (req, res) => {
    const { name, email, password, role, org_id } = req.body;
    const orgValue = org_id || null;

    try {
        await pool.query(
            "INSERT INTO users (name, email, password, role, org_id) VALUES ($1, $2, $3, $4, $5)",
            [name, email, password, role, orgValue]
        );
        res.redirect('/admin/users');
    } catch (err) {
        console.error(err);

        if (err.code === '23505') {
            // Email already exists
            return res.send("Error: Email already exists");
        }

        res.send("Error creating user");
    }
});


// ================= SHOW EDIT FORM =================
router.get('/edit/:id', adminOnly, async (req, res) => {
    try {
        const userResult = await pool.query(
            'SELECT * FROM users WHERE user_id = $1',
            [req.params.id]
        );
        const orgsResult = await pool.query(
            'SELECT org_id, name FROM organizations ORDER BY org_id ASC'
        );

        if (!userResult.rows[0]) return res.send("User not found");

        res.render('user_form', {
            user: userResult.rows[0],
            formAction: `/admin/users/edit/${req.params.id}`,
            admin: req.session.admin,
            orgs: orgsResult.rows || []
        });
    } catch (err) {
        console.error(err);
        res.send("Error loading form");
    }
});

// ================= UPDATE USER =================
router.post('/edit/:id', adminOnly, async (req, res) => {
    const { name, email, role, org_id, password } = req.body;
    const orgValue = org_id || null;

    try {
        if (password && password.trim() !== "") {
            // Update with password
            await pool.query(
                "UPDATE users SET name=$1, email=$2, role=$3, password=$4, org_id=$5 WHERE user_id=$6",
                [name, email, role, password, orgValue, req.params.id]
            );
        } else {
            // Update without password
            await pool.query(
                "UPDATE users SET name=$1, email=$2, role=$3, org_id=$4 WHERE user_id=$5",
                [name, email, role, org_id, req.params.id]
            );
        }

        res.redirect('/admin/users');
    } catch (err) {
        console.error(err);
        res.send("Error updating user");
    }
});

// ================= DELETE USER =================
router.post('/delete/:id', adminOnly, async (req, res) => {
    try {
        await pool.query(
            "DELETE FROM users WHERE user_id=$1",
            [req.params.id]
        );
        res.redirect('/admin/users');
    } catch (err) {
        console.error(err);
        res.send("Error deleting user");
    }
});

module.exports = router;
