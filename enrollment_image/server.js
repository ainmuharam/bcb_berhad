// server.js

const express = require('express');
const bodyParser = require('body-parser');
const fs = require('fs');
const path = require('path');
const mysql = require('mysql');

const app = express();
const PORT = process.env.PORT || 3000;

// Middleware
app.use(bodyParser.json({ limit: '10mb' })); // Increase limit for image data
app.use(express.static('public')); // Serve static files

// MySQL connection
const db = mysql.createConnection({
    host: 'localhost',
    user: 'root', // Replace with your MySQL username
    password: '', // Replace with your MySQL password
    database: 'bcb_berhad' // Replace with your MySQL database name
});

db.connect((err) => {
    if (err) {
        console.error('MySQL connection error:', err);
        return;
    }
    console.log('MySQL connected successfully');
});

// Endpoint to save the image
app.post('/save-image', (req, res) => {
    const imageData = req.body.image;

    // Remove the data URL prefix
    const base64Data = imageData.replace(/^data:image\/jpeg;base64,/, "");

    // Create a unique filename
    const fileName = `image_${Date.now()}.jpeg`;
    const filePath = path.join(__dirname, 'enrollment_image/uploads', fileName);

    // Save the image to the file system
    fs.writeFile(filePath, base64Data, 'base64', (err) => {
        if (err) {
            return res.status(500).send('Error saving image');
        }

        // Save the file path to the MySQL database
        const sql = 'INSERT INTO images (file_path) VALUES (?)';
        db.query(sql, [filePath], (err, result) => {
            if (err) {
                return res.status(500).send('Error saving file path to database');
            }

            // Respond with the file path
            res.json({ filePath: filePath });
        });
    });
});

// Start the server
app.listen (PORT, () => {
    console.log(`Server is running on http://localhost:${PORT}`);
});