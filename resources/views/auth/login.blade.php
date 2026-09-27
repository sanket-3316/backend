<!DOCTYPE html>
<html lang="en" data-mdb-theme="light">

<head>
    <meta charset="UTF-8">
    <title>Login</title>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.0/mdb.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.0/mdb.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <style>
        #intro {
            background-image: url(/asset/images/login.jpg);
            height: 100vh;
        }
    </style>
</head>

<body>
    <div id="intro" class="bg-image shadow-2-strong">
        <div class="mask d-flex align-items-center h-100" style="background-color: rgba(0, 0, 0, 0.8);">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-xl-5 col-md-8">
                        <div class="card text-center">
                            <div class="card-header">LOGIN</div>
                            <div class="card-body">
                                <form class="bg-white  p-5" method="POST"
                                    action="{{ url('login') }}">
                                    @csrf
                                    <!-- Email input -->
                                    <div class="form-outline mb-4" data-mdb-input-init>
                                        <input type="email" id="email" name="email" class="form-control" />
                                        <label class="form-label" for="email">Email address</label>
                                    </div>

                                    <!-- Password input -->
                                    <div class="form-outline mb-4 position-relative" data-mdb-input-init>
                                        <input type="password" name="password" id="password" class="form-control" style="padding-right: 40px;" />
                                        <label class="form-label" for="password">Password</label>
                                        <span id="togglePassword"
                                            style="position: absolute; top: 50%; right: 14px; transform: translateY(-50%); cursor: pointer; z-index: 5;">
                                            <i class="fa fa-eye" id="togglePasswordIcon"></i>
                                        </span>
                                    </div>

                                    <!-- 2 column grid layout for inline styling -->
                                    <div class="row mb-4">
                                        <div class="col d-flex justify-content-center">
                                            @if (session('error'))
                                                <p class="text-danger">{{ session('error') }}</p>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Submit button -->
                                    <button type="submit" class="btn btn-primary btn-block" data-mdb-ripple-init>Sign
                                        in</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Background image -->
    <!--Main Navigation-->

    <footer>

        <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.0/mdb.min.css" rel="stylesheet">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.0/mdb.min.js"></script>

        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

    </footer>

    <script>
        document.getElementById('togglePassword').addEventListener('click', function() {
            var input = document.getElementById('password');
            var icon = document.getElementById('togglePasswordIcon');

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    </script>
</body>

</html>
