const mongoose = require('mongoose');

const CategorySchema = new mongoose.Schema({
  name: { type: String, required: true },
  photo: { type: String },
  description: { type: String },
  isActive: { type: Number, default: 0 }, // 0 = active (keeps same semantics as PHP)
  created_at: { type: Date, default: Date.now }
});

module.exports = mongoose.model('Category', CategorySchema);
