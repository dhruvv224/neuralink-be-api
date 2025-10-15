// Simple seed script to create sample category and product
const mongoose = require('mongoose');
const dotenv = require('dotenv');
const connectDB = require('./config/db');
const Category = require('./models/Category');
const Product = require('./models/Product');

dotenv.config();
(async () => {
  await connectDB();
  try {
    const cat = new Category({ name: 'Home Furniture', description: 'Sample category', isActive: 0 });
    await cat.save();
    const prod = new Product({ cat_id: cat._id, name: 'Sample Sofa', short_description: 'Comfortable sofa', description: 'A comfortable 3-seater sofa', isActive: 0 });
    await prod.save();
    console.log('Seed complete');
    process.exit(0);
  } catch (err) {
    console.error(err);
    process.exit(1);
  }
})();
