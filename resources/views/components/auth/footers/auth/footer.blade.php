<footer class="footer py-4  ">
    <div class="container-fluid">
      <div class="row align-items-center justify-content-lg-between">
        <div class="col-lg-6 mb-lg-0 mb-4">
          <div class="copyright text-center text-sm text-muted text-lg-start">
            {{-- Year rendered server side so it shows without JavaScript and for crawlers. --}}
            © {{ date('Y') }} Divers Hub
            <span class="mx-1 opacity-5">|</span> <x-version />
          </div>
        </div>
        <div class="col-lg-6">
          <ul class="nav nav-footer justify-content-center justify-content-lg-end">
          <li class="nav-item">
              <a href="/home" class="nav-link text-muted">Home</a>
            </li>
            <li class="nav-item">
              <a href="/AboutUs" class="nav-link text-muted">About us</a>
            </li>
            <li class="nav-item">
              <a href="/AboutUs" class="nav-link text-muted">Contact</a>
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