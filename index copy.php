<?php

include 'config.php';
include 'js_php/database.php';

date_default_timezone_set('Asia/Manila');
$currentHour = date('G');

if ($currentHour >= 5 && $currentHour < 12) {
  $greeting = "Good Morning!";
} elseif ($currentHour >= 12 && $currentHour < 18) {
  $greeting = "Good Afternoon!";
} else {
  $greeting = "Good Evening!";
}

?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $title; ?> | Login</title>
  <?php include 'tailwind_config.php'; ?>
</head>

<body class="bg-base-200">

  <div class="flex h-screen">
    <div class="hidden lg:flex items-center justify-center flex-1 bg-base-100 text-black">
      <div class="max-w-md text-center">
        <div class="carousel w-full">
          <div id="item1" class="carousel-item w-full">
            <img src="/assets/images/logo.png" class="w-full mask mask-square" />
          </div>
          <div id="item2" class="carousel-item w-full">
            <div>
              <div class="chat chat-start">
                <div class="chat-bubble chat-bubble-primary">What kind of nonsense is this</div>
              </div>
              <div class="chat chat-start">
                <div class="chat-bubble chat-bubble-secondary">Put me on the Council and not make me a Master!??</div>
              </div>
              <div class="chat chat-start">
                <div class="chat-bubble chat-bubble-accent">That's never been done in the history of the Jedi.</div>
              </div>
              <div class="chat chat-start">
                <div class="chat-bubble chat-bubble-neutral">It's insulting!</div>
              </div>
              <div class="chat chat-end">
                <div class="chat-bubble chat-bubble-info">Calm down, Anakin.</div>
              </div>
              <div class="chat chat-end">
                <div class="chat-bubble chat-bubble-success">You have been given a great honor.</div>
              </div>
              <div class="chat chat-end">
                <div class="chat-bubble chat-bubble-warning">To be on the Council at your age.</div>
              </div>
              <div class="chat chat-end">
                <div class="chat-bubble chat-bubble-error">It's never happened before.</div>
              </div>
              <div class="chat chat-start">
                <div class="chat-bubble chat-bubble-primary">What kind of nonsense is this</div>
              </div>

              <div class="chat chat-start">
                <div class="chat-bubble chat-bubble-neutral">It's insulting!</div>
              </div>
            </div>
          </div>
          <div id="item3" class="carousel-item w-full">
            <ul class="timeline timeline-vertical">
              <li>
                <div class="timeline-start timeline-box ">First Macintosh computer
                </div>
                <div class="timeline-middle">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                    class="text-primary h-5 w-5">
                    <path fill-rule="evenodd"
                      d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z"
                      clip-rule="evenodd" />
                  </svg>
                </div>
                <hr class="bg-primary" />
              </li>
              <li>
                <hr class="bg-primary" />
                <div class="timeline-middle">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                    class="text-primary h-5 w-5">
                    <path fill-rule="evenodd"
                      d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z"
                      clip-rule="evenodd" />
                  </svg>
                </div>
                <div class="timeline-end timeline-box">iMac</div>
                <hr class="bg-primary" />
              </li>
              <li>
                <hr class="bg-primary" />
                <div class="timeline-start timeline-box">iPod</div>
                <div class="timeline-middle">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                    class="text-primary h-5 w-5">
                    <path fill-rule="evenodd"
                      d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z"
                      clip-rule="evenodd" />
                  </svg>
                </div>
                <hr />
              </li>
              <li>
                <hr />
                <div class="timeline-middle">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-5 w-5">
                    <path fill-rule="evenodd"
                      d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z"
                      clip-rule="evenodd" />
                  </svg>
                </div>
                <div class="timeline-end timeline-box">iPhone</div>
                <hr />
              </li>
              <li>
                <hr />
                <div class="timeline-start timeline-box">Apple Watch</div>
                <div class="timeline-middle">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-5 w-5">
                    <path fill-rule="evenodd"
                      d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z"
                      clip-rule="evenodd" />
                  </svg>
                </div>
              </li>
            </ul>
          </div>

        </div>
        <div class="flex w-full justify-center gap-2 py-2">
          <a href="#item1" class="btn btn-xs btn-circle">1</a>
          <a href="#item2" class="btn btn-xs btn-circle">2</a>
          <a href="#item3" class="btn btn-xs btn-circle">3</a>
        </div>

      </div>
    </div>
    <div class="w-full bg-base-200 lg:w-1/2 flex items-center justify-center">
      <div class="max-w-md w-full p-6">
        <h1 class="text-2xl font-semibold mb-6 color-base-content text-center"><?php echo $greeting; ?> Welcome Back
        </h1>

        <!-- <form action="../../dashboard" method="POST" class="space-y-4"> -->
        <form action="login.php" method="POST" class="space-y-4">


          <div>
            <label for="email" class="block text-sm font-medium color-base-content">Email</label>
            <input type="text" id="email" name="email" class="input input-primary mt-1 p-2 w-full border rounded-md" />
          </div>

          <div>
            <label for="password" class="block text-sm font-medium color-base-content">Password</label>
            <input type="password" id="password" name="password"
              class="input input-primary mt-1 p-2 w-full border rounded-md">
          </div>
          <div>
            <button type="submit" name="submit" value="login"
              class="w-full btn btn-primary text-white p-2 rounded-md">Log In</button>
          </div>
        </form>

      </div>
      <div class="absolute top-4 right-4"><label class="swap swap-rotate">
          <input type="checkbox" class="theme-controller" value="light" />

          <svg class="swap-off h-10 w-10 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
            <path
              d="M5.64,17l-.71.71a1,1,0,0,0,0,1.41,1,1,0,0,0,1.41,0l.71-.71A1,1,0,0,0,5.64,17ZM5,12a1,1,0,0,0-1-1H3a1,1,0,0,0,0,2H4A1,1,0,0,0,5,12Zm7-7a1,1,0,0,0,1-1V3a1,1,0,0,0-2,0V4A1,1,0,0,0,12,5ZM5.64,7.05a1,1,0,0,0,.7.29,1,1,0,0,0,.71-.29,1,1,0,0,0,0-1.41l-.71-.71A1,1,0,0,0,4.93,6.34Zm12,.29a1,1,0,0,0,.7-.29l.71-.71a1,1,0,1,0-1.41-1.41L17,5.64a1,1,0,0,0,0,1.41A1,1,0,0,0,17.66,7.34ZM21,11H20a1,1,0,0,0,0,2h1a1,1,0,0,0,0-2Zm-9,8a1,1,0,0,0-1,1v1a1,1,0,0,0,2,0V20A1,1,0,0,0,12,19ZM18.36,17A1,1,0,0,0,17,18.36l.71.71a1,1,0,0,0,1.41,0,1,1,0,0,0,0-1.41ZM12,6.5A5.5,5.5,0,1,0,17.5,12,5.51,5.51,0,0,0,12,6.5Zm0,9A3.5,3.5,0,1,1,15.5,12,3.5,3.5,0,0,1,12,15.5Z" />
          </svg>

          <svg class="swap-on h-10 w-10 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
            <path
              d="M21.64,13a1,1,0,0,0-1.05-.14,8.05,8.05,0,0,1-3.37.73A8.15,8.15,0,0,1,9.08,5.49a8.59,8.59,0,0,1,.25-2A1,1,0,0,0,8,2.36,10.14,10.14,0,1,0,22,14.05,1,1,0,0,0,21.64,13Zm-9.5,6.69A8.14,8.14,0,0,1,7.08,5.22v.27A10.15,10.15,0,0,0,17.22,15.63a9.79,9.79,0,0,0,2.1-.22A8.11,8.11,0,0,1,12.14,19.73Z" />
          </svg>
        </label></div>

    </div>

  </div>
</body>
<script>

</script>

</html>