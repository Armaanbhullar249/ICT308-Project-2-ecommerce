/* Kushal — login + create account */

(function () {

  const loginView = document.getElementById("login-view");
  const signupView = document.getElementById("signup-view");

  const loginTab = document.getElementById("show-login");
  const signupTab = document.getElementById("show-signup");


  /*
   * Show signup section
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
   * Show login section
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
   * Automatically show signup when
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
   * Redirect users who are already logged in
   */
  if (Warners.hasAccountSession()) {

    location.href =
      Warners.afterLoginPath(
        Warners.getRole()
      );
  }


  /*
   * Render navigation/header
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
   * =====================================================
   * LOGIN
   * =====================================================
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
            .getElementById(
              "username"
            )
            .value
            .trim();


        const password =
          document
            .getElementById(
              "password"
            )
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
   * =====================================================
   * CREATE ACCOUNT
   * =====================================================
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
         * Read registration fields
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
         * Require email
         */
        if (!email) {

          err.hidden = false;

          err.textContent =
            "Email address is required.";

          return;
        }


        /*
         * HTML5 browser email validation
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
         * Password must contain:
         * - one lowercase letter
         * - one uppercase letter
         * - one symbol
         * - minimum 4 characters
         */
        const passwordPattern =
          /^(?=.*[a-z])(?=.*[A-Z])(?=.*[^A-Za-z0-9]).{4,}$/;


        if (
          !passwordPattern.test(
            password
          )
        ) {

          err.hidden = false;

          err.textContent =
            "Password must contain at least 1 uppercase letter, 1 lowercase letter, and 1 symbol.";

          return;
        }


        /*
         * Confirm passwords match
         */
        if (password !== confirm) {

          err.hidden = false;

          err.textContent =
            "Passwords do not match. Please try again.";

          return;
        }


        /*
         * Register customer through backend
         */
        const result =
          Warners.registerCustomer({
            name,
            email,
            username,
            password,
          });


        /*
         * Registration failed
         */
        if (!result.ok) {

          err.hidden = false;

          err.textContent =
            result.error ||
            "Could not create your account.";

          return;
        }


        /*
         * Automatically login after signup
         */
        const user =
          Warners.login(
            username,
            password
          );


        /*
         * If automatic login fails,
         * account still exists.
         */
        if (!user) {

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