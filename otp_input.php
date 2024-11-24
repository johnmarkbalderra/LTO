<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>OTP Verification</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css" />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i&display=swap"
    />
    <link rel="icon" type="image/jpg" href="assets/img/LTO-logo.jpg" />
  </head>
  <body class="bg-gradient-primary">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-md-9 col-lg-12 col-xl-10">
          <div class="card shadow-lg o-hidden border-0 my-5">
            <div class="card-body p-0">
              <div class="row">
                <div class="col-lg-6 d-none d-lg-flex">
                  <div
                    class="flex-grow-1 bg-login-image"
                    style="
                      background-image: url('assets/img/LTO-logo.jpg');
                      margin: 0px;
                      height: auto;
                      width: auto;
                      padding-top: 43px;
                      padding-bottom: 485px;
                      padding-right: 0px;
                      margin-right: -53px;
                    "
                  ></div>
                </div>
                
                <div class="col-lg-6">
                  <div class="p-xxl-5">
                    <div class="text-center">
                      <h4 class="text-dark mb-4">OTP Verification</h4>
                    </div>
                    <hr>
                    <div
                      id="error-message"
                      class="alert alert-danger"
                      style="display: none"
                    ></div>
                    <form action="verify_otp.php" method="post">
                        <label for="otp">Enter OTP:</label>
                        <input type="text" id="otp" name="otp" required>
                        <button type="submit">Verify</button>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <script src="assets/bootstrap/js/bootstrap.min.js"></script>
    <script src="assets/js/bs-init.js"></script>
    <script src="assets/js/theme.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
      $(document).ready(function () {
        $("#login-form").submit(function (event) {
          event.preventDefault();

          $.ajax({
            type: "POST",
            url: "login.php",
            data: $(this).serialize(),
            dataType: "json",
            success: function (response) {
              if (response.success) {
                // Redirect to a new page or perform other actions on successful login
                window.location.href = response.redirect;
              } else {
                $("#error-message").text(response.message).show();
              }
            },
          });
        });
      });
    </script>
  </body>
</html>
