<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= esc($title); ?></title>
  <link rel="stylesheet" href="/css/style.css">
</head>
<body>

  <?= $this->include('template/header'); ?>

  <section id="contact-hero">
    <h1>CONTACT US</h1>
    <p>We'd love to hear from you. Reach out with any questions or ideas.</p>
  </section>

  <section id="contact-wrapper">
    <div id="contact-info">
      <p><strong>Email:</strong><br>
        <a href="mailto:nowastehelp@gmail.com">nanda@gmail.com</a></p>
      <p><strong>Phone:</strong><br>
        <a href="tel:+1989353422">+082194673477</a></p>
      <p><strong>Address:</strong><br>
        403, Magnetic Drive, Unit 2<br>
        Toronto, Ontario</p>
    </div>

    <div id="contact-form">
      <form action="/contact/send" method="post">
        <div class="form-row">
          <input type="text" name="name" placeholder="name" required>
          <input type="email" name="email" placeholder="e-mail" required>
        </div>
        <div class="form-row">
          <textarea name="message" placeholder="your message" required></textarea>
        </div>
        <button type="submit">SEND</button>
      </form>
    </div>
  </section>

  <!-- Peta -->
  <section id="contact-map">
    <h2>Our Location</h2>
    <div class="map-container">
      <iframe
        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2886.573223898298!2d-79.48726128450119!3d43.7645002791178!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x882b2d93db21a0d1%3A0x31f7b8298482ad50!2sMagnetic%20Dr%2C%20Toronto%2C%20ON%20M3J%202C4%2C%20Canada!5e0!3m2!1sen!2sca!4v1685983282641!5m2!1sen!2sca"
        width="100%" height="400" style="border:0;" allowfullscreen="" loading="lazy">
      </iframe>
    </div>
  </section>

  <?= $this->include('template/footer'); ?>

</body>
</html>