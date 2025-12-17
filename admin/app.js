const express = require('express');
const session = require('express-session');
const path = require('path');

const authRoutes = require('./routes/auth');
const userCRUDRoutes = require('./routes/usersCRUD');

const app = express();

// pug
app.set('view engine', 'pug');
app.set('views', path.join(__dirname, 'views'));

app.use(express.urlencoded({ extended: true }));
app.use(express.static('public'));

app.use(session({
    secret: "secret",
    resave: false,
    saveUninitialized: false
}));

// routes
app.use('/admin', authRoutes);
app.use('/admin/users', userCRUDRoutes);

// default route
app.get('/', (req, res) => {
    res.redirect('/admin/login');
});

// server
app.listen(3000, () => {
    console.log("Admin module running on port 3000");
});
