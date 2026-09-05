const express = require('express');
const app = express();
const PORT = process.env.PORT || 3000;

app.use(express.json());

app.get('/', (req, res) => {
  res.json({ message: 'AntrolService is running' });
});

app.listen(PORT, () => {
  console.log(`AntrolService listening on port ${PORT}`);
});
