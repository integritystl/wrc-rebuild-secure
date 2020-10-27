<html>
    <head>
        <style>
            .return-page { background: #F1F1F1; font-family: "Open Sans",Helvetica,Arial,sans-serif; font-size: 1em; text-align:center; display:block; }
            .return-page-action { margin-top: 2em; }
            .return-page-action a { text-decoration: none; color: #FFF; font-size: 1em; text-transform: uppercase; padding: 0.8em 1.5em; background: #444; position: relative; }
            .return-page-action a:hover { cursor: pointer; background: #6E6E6E; }
            .return-page-content { width: 50%; position: relative; background: #FFF; margin: 100px auto; padding: 15px; border: 1px solid #ccc; -webkit-box-shadow: 0 0 2px #cccccc; -moz-box-shadow: 0 0 2px #cccccc; box-shadow: 0 0 2px #cccccc; }
            .return-page-hdr { clear: none; font-size: 1.3em; margin: 1em 0 1em; color: #777; }
            .return-page-info-dv { clear: both; position: relative; padding: 1.5em 2em 1em; margin: 1em 0 2em; border: 1px solid #eee; border-radius: 2px; }
            .return-page-attention { width: 100%; padding: .75em 2.5%; margin: 0 auto 1em; border: 4px solid #F8D755; color: #555; font-size: 1em; line-height: 1.6em; text-align: center; -webkit-box-sizing: border-box; -moz-box-sizing: border-box; box-sizing: border-box; }
        </style>
        <!-- Do an automatic redirect. -->
        <script type="text/javascript">
            window.location.href = '%%return_url%%';
        </script>
    </head>
    <body class="return-page">
        <!-- Allow a manual redirect, just in case... -->
        <div class="return-page-content">

            <div class="return-page-info-dv">
                <h4 class="return-page-hdr">
                    Redirecting back to '%%website_name%%' ...
                </h4>
                <p class="return-page-attention">
                    If you are not automatically redirected back to the registration website within 10 seconds <strong>Please click the button below</strong>.
                </p>
                <p class="return-page-action">
                    <a href="%%return_url%%">
                        Back to the registration website
                    </a>
                </p>
            </div>

        </div>
    </body>
</html>