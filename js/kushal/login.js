/* Kushal — login + create account */

(function () {

  const loginView = document.getElementById("login-view");
  const signupView = document.getElementById("signup-view");

  const loginTab = document.getElementById("show-login");
  const signupTab = document.getElementById("show-signup");


  /*
   * Show account creation form
   */
  function showSignup() {

    loginView.hidden = true;
    signupView.hidden = false;

    loginTab.classList.remove("active");
    signupTab.classList.add("active");

    loginTab.setAttribute(
      "aria-selected",
      "false"
    );

    signupTab.setAttribute(
      "aria-selected",
      "true"
    );

    Warners.animateView(signupView);
  }


  /*
   * Show login form
   */
  function showLogin() {

    signupView.hidden = true;
    loginView.hidden = false;

    signupTab.classList.remove("active");
    loginTab.classList.add("active");

    signupTab.setAttribute(
      "aria-selected",
      "false"
    );

    loginTab.setAttribute(
      "aria-selected",
      "true"
    );

    Warners.animateView(loginView);
  }


  /*
   * Open signup tab automatically when
   * login.html?signup=1 is used
   */
  if (
    new URLSearchParams(
      location.search
    ).get("signup") === "1"
  ) {
    showSignup();
  }


  /*
   * Already logged-in users do not
   * need to see the login page
   */
  if (Warners.hasAccountSession()) {

    location.href =
      Warners.afterLoginPath(
        Warners.getRole()
      );
  }


  /*
   * Render shared navigation/header
   */
  Warners.renderHeader("login");


  /*
   * Tab controls
   */
  signupTab.addEventListener(
    "click",
    showSignup
  );

  loginTab.addEventListener(
    "click",
    showLogin
  );


  /*
   * LOGIN
   */
  document
    .getElementById("login-form")
    .addEventListener(
      "submit",
      (e) => {

        e.preventDefault();

        const err =
          document.getElementById(
            "error"
          );

        err.hidden = true;
        err.textContent = "";


        const username =
          document
            .getElementById("username")
            .value
            .trim();

        const password =
          document
            .getElementById("password")
            .value;


        const user =
          Warners.login(
            username,
            password
          );


        if (!user) {

          err.hidden = false;

          err.textContent =
            "We couldn't sign you in. Check your details or create an account first.";

          return;
        }


        location.href =
          Warners.afterLoginPath(
            user.role
          );
      }
    );


  /*
   * CREATE ACCOUNT
   */
  document
    .getElementById("signup-form")
    .addEventListener(
      "submit",
      (e) => {

        e.preventDefault();


        const err =
          document.getElementById(
            "signup-error"
          );

        err.hidden = true;
        err.textContent = "";


        /*
         * Read form fields
         */
        const name =
          document
            .getElementById(
              "full-name"
            )
            .value
            .trim();


        const email =
          document
            .getElementById(
              "new-email"
            )
            .value
            .trim()
            .toLowerCase();


        const username =
          document
            .getElementById(
              "new-username"
            )
            .value
            .trim()
            .toLowerCase();


        const password =
          document
            .getElementById(
              "new-password"
            )
            .value;


        const confirm =
          document
            .getElementById(
              "confirm-password"
            )
            .value;


        /*
         * Required email check
         */
        if (!email) {

          err.hidden = false;

          err.textContent =
            "Email address is required.";

          return;
        }


        /*
         * Browser normally performs this
         * automatically because input
         * type=email is used.
         *
         * This gives an additional
         * JavaScript check.
         */
        const emailInput =
          document.getElementById(
            "new-email"
          );


        if (!emailInput.checkValidity()) {

          err.hidden = false;

          err.textContent =
            "Please enter a valid email address.";

          emailInput.focus();

          return;
        }


        /*
         * Password confirmation
         */
        if (password !== confirm) {

          err.hidden = false;

          err.textContent =
            "Passwords do not match. Please try again.";

          return;
        }


        /*
         * Register through PHP backend
         */
        const result =
          Warners.registerCustomer({
            name,
            email,
            username,
            password,
          });


        if (!result.ok) {

          err.hidden = false;

          err.textContent =
            result.error ||
            "Could not create your account.";

          return;
        }


        /*
         * Automatically login after
         * successful registration
         */
        const user =
          Warners.login(
            username,
            password
          );


        if (!user) {

          /*
           * Account was created but an
           * automatic login failed.
           * User can simply sign in.
           */
          showLogin();

          document.getElementById(
            "username"
          ).value = username;

          const loginError =
            document.getElementById(
              "error"
            );

          loginError.hidden = false;

          loginError.textContent =
            "Your account was created successfully. Please sign in.";

          return;
        }


        /*
         * Redirect according to role
         */
        location.href =
          Warners.afterLoginPath(
            user.role
          );
      }
    );

})();