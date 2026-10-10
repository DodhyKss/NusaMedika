const express = require('express');
const app = express();
const PORT = process.env.PORT || 3000;

app.use(express.json());

app.get('/', (req, res) => {
  res.json({ message: 'Bridging Service is running' });
});

app.listen(PORT, () => {
  console.log(`Bridging Service listening on port ${PORT}`);
});
