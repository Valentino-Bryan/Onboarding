<!DOCTYPE html>
<html lang="nl">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Gebruiker Aanmaken - TechnoLab</title>

  <!-- Link naar jouw CSS (bestandsnaam: add.css) -->
  <link rel="stylesheet" href="../assets/css/add.css">

  <!-- Font -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
</head>
<body>

  <div class="page-wrapper">

    <!-- Achtergrond blobs -->
    <div class="blob blob-purple" aria-hidden="true"></div>
    <div class="blob blob-green" aria-hidden="true"></div>

    <!-- Centraal content gebied -->
    <div class="form-wrapper">


      <!-- Card -->
      <div class="card">
        <main>
          <div class="title-section">
            <h1>Creëer een account</h1>
            <p>Voeg een nieuwe organisator toe aan het platform</p>
          </div>

          <form class="form" autocomplete="off" novalidate>
            <div class="form-group">
              <label for="naam">Volledige Naam</label>
              <input id="naam" name="naam" type="text" placeholder="Jan de Vries" required>
            </div>

            <div class="form-group">
              <label for="email">E-mailadres</label>
              <input id="email" name="email" type="email" placeholder="jan.devries@example.com" required>
            </div>

            <div class="form-group">
              <label for="password">Wachtwoord</label>
              <input id="password" name="password" type="password" placeholder="••••••••" required>
            </div>

            <div class="form-group">
              <label for="role">Rol</label>
              <select id="role" name="role" aria-label="Rol">
                <option>Organisator</option>
                <option>Beheerder</option>
                <option>Gast</option>
              </select>
            </div>

            <div class="button-wrapper">
              <button type="submit">Aanmaken</button>
            </div>
          </form>
        </main>
      </div>

    </div>
  </div>

</body>
</html>
