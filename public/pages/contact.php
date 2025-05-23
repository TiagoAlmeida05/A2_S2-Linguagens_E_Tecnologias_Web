<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/utils.php';
include_once __DIR__ . '/../templates/header.php';
?>

<style>
  .contact-container {
    max-width: 700px;
    margin: 3rem auto;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 8px 24px rgb(0 0 0 / 0.1);
    padding: 2rem 3rem;
    font-family: 'Poppins', sans-serif;
  }

  .contact-container h1 {
    font-weight: 600;
    font-size: 2.2rem;
    margin-bottom: 1rem;
    color: #333;
    text-align: center;
  }

  .contact-container p {
    text-align: center;
    margin-bottom: 2rem;
    font-size: 1.1rem;
    color: #555;
  }

  table.contact-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0 15px;
  }

  table.contact-table th,
  table.contact-table td {
    padding: 1rem 1.5rem;
    background: #f9f9f9;
    border-radius: 8px;
    text-align: left;
    color: #444;
    box-shadow: 0 2px 6px rgb(0 0 0 / 0.05);
  }

  table.contact-table th {
    background: #e1e7f0;
    color: #1a237e;
    font-weight: 600;
    font-size: 1rem;
  }

  table.contact-table td a {
    color: #1a73e8;
    text-decoration: none;
    font-weight: 500;
  }

  table.contact-table td a:hover {
    text-decoration: underline;
  }

  @media (max-width: 600px) {
    .contact-container {
      padding: 1rem 1.5rem;
      margin: 1.5rem auto;
    }

    table.contact-table th,
    table.contact-table td {
      padding: 0.75rem 1rem;
      font-size: 0.9rem;
    }
  }
</style>

<div class="contact-container">
  <h1>Contact Information</h1>
  <p>If you have any questions or need support, feel free to reach out to our team members:</p>
  
  <table class="contact-table" aria-label="Contact Information Table">
    <thead>
      <tr>
        <th>Name</th>
        <th>Student ID</th>
        <th>Email</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>Tiago Pinto Cardoso de Almeida</td>
        <td>202303450</td>
        <td><a href="mailto:up202303450@edu.fe.up.pt">up202303450@edu.fe.up.pt</a></td>
      </tr>
      <tr>
        <td>Tiago Mota Cunha</td>
        <td>202305564</td>
        <td><a href="mailto:up202305564@edu.fe.up.pt">up202305564@edu.fe.up.pt</a></td>
      </tr>
      <tr>
        <td>Filipe Lemos Paiva</td>
        <td>202304284</td>
        <td><a href="mailto:up202304284@edu.fe.up.pt">up202304284@edu.fe.up.pt</a></td>
      </tr>
    </tbody>
  </table>
</div>

<?php include_once __DIR__ . '/../templates/footer.php'; ?>
