const express = require('express');
const session = require('express-session');
const path = require('path');

const adminAuthRoutes = require('./routes/adminAuth');
const adminUsersRoutes = require('./routes/adminUsers');

const app = express();

// View engine (PUG)
app.set('view engine', 'pug');
app.set('views', path.join(__dirname, 'views'));

// Middleware
app.use(express.urlencoded({ extended: true }));
app.use(express.static('public'));

app.use(session({
    secret: "secret",
    resave: false,
    saveUninitialized: false
}));

// Routes
app.use('/admin', adminAuthRoutes);
app.use('/admin/users', adminUsersRoutes);

// Default route
app.get('/', (req, res) => {
    res.redirect('/admin/login');
});

// Server
app.listen(3000, () => {
    console.log("Admin module running on port 3000");
});
