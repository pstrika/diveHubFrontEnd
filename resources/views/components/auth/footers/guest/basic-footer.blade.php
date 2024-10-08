@props(['textColor'])
<footer class="footer position-absolute bottom-2 py-2 w-100">
    <div class="container">
      <div class="row align-items-center justify-content-lg-between">
        <div class="col-12 col-md-6 my-auto">
          <div class="{{ $textColor}} copyright text-center text-sm text-lg-start">
            © <script>
              document.write(new Date().getFullYear())
            </script>,
            Divers Hub
          </div>
        </div>
        <div class="col-12 col-md-6">
          <ul class="nav nav-footer justify-content-center justify-content-lg-end">
            <li class="nav-item">
              <a href="/home" class="nav-link text-muted">Home</a>
            </li>
            <li class="nav-item">
              <a href="/TermsOfUse" class="nav-link text-muted">Terms of use</a>
            </li>
            <li class="nav-item">
              <a href="/PrivacyPolicy" class="nav-link text-muted">Privacy Policy</a>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </footer>