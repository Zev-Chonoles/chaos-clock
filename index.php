<!doctype html>

<html lang="en-US">

<head prefix="og: http://ogp.me/ns#">

  <title>Chaos Clock</title>

  <meta name="author" content="Zev Chonoles">

  <meta name="description" content="A binary clock seeding a rule 30 cellular automaton.">

  <meta name="keywords" content="Zev Chonoles, math, binary clock, chaos, rule 30, cellular automata">

  <link rel="shortcut icon" type="image/x-icon" href="http://zevchonoles.org/chaos-clock/favicon.ico">

  <link rel='stylesheet' type='text/css' href='http://fonts.googleapis.com/css?family=Lato'>
<?php
  $pixel_size    = 24;
  $grid_size     = 18;
  $digits_height =  3;
  $line_width    =  5;
  $digit_bottom_margin = 20;
?>

  <!--                  Overall design                    -->
  <!-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -->
  <!--  "Pixel" width/height:         <?php echo $pixel_size; ?>px                -->
  <!--  Grid, in "pixels":            <?php echo $grid_size; ?> by <?php echo $grid_size; ?>            -->
  <!--  Digit readout, in "pixels":   <?php echo $grid_size; ?> by <?php echo $digits_height; ?>             -->
  <!--  Width of the whole thing:     <?php echo $pixel_size; ?>px * <?php echo $grid_size; ?> = <?php echo $pixel_size * $grid_size; ?>px   -->
  <!--  Height of the whole thing:    <?php echo $pixel_size; ?>px * <?php echo $grid_size + $digits_height; ?> = <?php echo $pixel_size * ($grid_size + $digits_height); ?>px   -->

  <style type="text/css">

    html, body, div, span, canvas
    {
      margin: 0;
      padding: 0;
      border: 0;
      font-family: 'Lato', 'Lucida Console', Monaco, monospace;
    }

    #wrapper
    {
      /* Horizontally and vertically centered, using method described in */
      /* http://www.smashingmagazine.com/2013/08/09/absolute-horizontal-vertical-centering-css/ */
      margin: auto;
      position: absolute;
      top: 0;
      bottom: 0;
      left: 0;
      right: 0;

      /* Fitting it exactly to the dimensions of the clock */
      width: <?php echo $pixel_size * $grid_size; ?>px;
      height: <?php echo $pixel_size * ($grid_size + $digits_height); ?>px;

      /* Leaving space at the bottom for explanation */
      padding-bottom: 100px;
    }

    canvas
    {
      display: block;
    }

    #explanation
    {
      margin: 0;
      position: absolute;
      bottom: 0;
      left: 0;
      right: 0;
      text-align: center;
    }

    #question
    {
      font-size: 40pt;
      font-weight: bold;
      color: #ccc;
      text-decoration: none;
      display: block;
    }

    #answer
    {
      display: none;
      color: #aaa;
    }

    #answer a, #answer a:visited
    {
      color: #888;
      text-decoration: underline;
    }

  </style>

  <!-- Open Graph -->
  <meta property="og:title" content="Chaos Clock">
  <meta property="og:type" content="website">
  <meta property="og:url" content="http://zevchonoles.org/chaos-clock/">
  <meta property="og:image" content="http://zevchonoles.org/chaos-clock/og.png">
  <meta property="og:site_name" content="Zev Chonoles">
  <meta property="og:description" content="A binary clock seeding a rule 30 cellular automaton.">

  <!-- Google Analytics -->
  <script>
    (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
    (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
    m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
    })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

    ga('create', 'UA-53874607-1', 'auto');
    ga('send', 'pageview');
  </script>

  <!-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- --
      This page is © <?php echo date("Y"); ?> Zev Chonoles, and licensed under Creative Commons Attribution 4.0.
      See a brief, easy-to-read summary here:

                           http://creativecommons.org/licenses/by/4.0/

      Basically, do anything you want with this as long as you give appropriate attribution.

      This work was partially inspired by Jacopo Colò's http://www.jacopocolo.com/hexclock/.
    -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -->

</head>

<body>

  <div id="wrapper">

    <!-- 18 "pixels" by 3 "pixels" -->
    <canvas id="digits" width="<?php echo $pixel_size * $grid_size; ?>" height="<?php echo $pixel_size * $digits_height; ?>"></canvas>

    <!-- 18 "pixels" by 18 "pixels" -->
    <canvas id="grid" width="<?php echo $pixel_size * $grid_size; ?>" height="<?php echo $pixel_size * $grid_size; ?>"></canvas>

    <div id="explanation">

      <a id="question" href="#" onclick="reveal_answer();">?</a>

      <span id="answer">Pixels in the top row represent the time in <a target="_blank" href="http://en.wikipedia.org/wiki/Binary_number">binary</a>.
      Then we see the first few steps of the <a target="_blank" href="http://en.wikipedia.org/wiki/Rule_30">rule 30 cellular automaton</a>
      using the time as the initial value. Small changes can have huge effects. Hence, "Chaos Clock".</span>

    </div>

  </div>

  <script type="text/javascript">

    // EXPLANATION
    // --------------------------------------------------------

    function reveal_answer()
    {
      var q = document.getElementById("question");
      var a = document.getElementById("answer");

      q.style.display = "none";
      a.style.display = "block";
    }  


    // DIGIT READOUT PREP
    // --------------------------------------------------------

    // Get the "digits" canvas
    var digits_canvas = document.getElementById("digits");
    var digits_context = digits_canvas.getContext("2d");


    // Set the font and alignment for the digits
    digits_context.font = "bold 40pt 'Lato', 'Lucida Console', Monaco, monospace";
    digits_context.textAlign = "center";


    // Set the colors for the digits and lines
    var hour_color = "#ff0000";
    var minute_color = "#ffcc00";
    var second_color = "#0066ff";


    // Set the line width (in px) for the lines under the digits
    digits_context.lineWidth = <?php echo $line_width; ?>;


    // Takes an integer and give the last two digits of its decimal expansion, padded with 0's
    function two_digit_decimal(n)
    {
      return ("0"+Number(n).toString()).slice(-2);
    }



    // GRID PREP
    // --------------------------------------------------------

    // Get the "grid" canvas
    var grid_canvas = document.getElementById("grid");
    var grid_context = grid_canvas.getContext("2d");


    // Turns on the "pixel" in position (i,j)
    // i = ROW number, j = COLUMN number
    function pixel_on(i,j)
    {
      // Note that 24 arises because one "pixel" is 24px by 24px
      grid_context.fillRect(<?php echo $pixel_size; ?>*j, <?php echo $pixel_size; ?>*i, <?php echo $pixel_size; ?>, <?php echo $pixel_size; ?>);
    }


    // Takes a string of 18 bits and prints them in "pixels" on the given row
    function print_row(row_num, eighteen_bits)
    {
      for(var i = 0; i < <?php echo $grid_size; ?>; i++)
      {
        if(eighteen_bits.charAt(i) === "1")
        {
          pixel_on(row_num,i);
        }
      }
    }


    // Implements "rule 30"
    // http://en.wikipedia.org/wiki/Rule_30
    function rule30(three_bits)
    {
      switch(three_bits)
      {
      	case "111":
      	  return "0";
      	case "110":
      	  return "0";
      	case "101":
      	  return "0";
      	case "100":
      	  return "1";
      	case "011":
      	  return "1";
      	case "010":
      	  return "1";
      	case "001":
      	  return "1";
      	case "000":
      	  return "0";
      }
    }


    // Takes a string of 18 bits and return its successor under rule 30
    // For bits on either end of the string, consider the string as wrapping around
    function successor_string(eighteen_bits)
    {
      var return_string = "";

      for(var i = 0; i < <?php echo $grid_size; ?>; i++)
      {
        return_string += rule30(eighteen_bits.charAt((i+<?php echo $grid_size - 1; ?>)%<?php echo $grid_size; ?>) + eighteen_bits.charAt(i) + eighteen_bits.charAt((i+1)%<?php echo $grid_size; ?>));
      }
      
      return return_string;
    }


    // Takes an integer and give the last six digits of its binary expansion, padded with 0's
    function six_digit_binary(n)
    {
      return ("000000"+Number(n).toString(2)).slice(-6);
    }



    // MAIN LOOP
    // --------------------------------------------------------

    // To readers / future me:
    //
    //  - "52" chosen by eyeballing (the height of the digit readout is 72px, numbers should be close to bottom,
    //    and canvas y-coordinates are backwards)
    //
    //  - The lengths of the lines are naturally 1/3 the size of the grid, i.e., (1/3) * 432px = 144px
    //
    //  - The horizontal coordinates of the digits are at the middle of the relevant location, e.g.,
    //    72px is at the middle of the space for the hour digits (from 0px to 144px)
    //
    //  - The vertical coordinates of the lines are chosen so that the *actual* bottom edge of the line is 
    //    exactly touching the grid (72px - (5px/2)). This is because of how pixels are drawn on the screen
    //    (http://diveintohtml5.info/canvas.html#pixel-madness)

    function draw()
    {
      requestAnimationFrame(draw);

      // Clear old image away
      digits_context.clearRect(0, 0, <?php echo $pixel_size * $grid_size; ?>, <?php echo $pixel_size * $digits_height; ?>);
      grid_context.clearRect(0, 0, <?php echo $pixel_size * $grid_size; ?>, <?php echo $pixel_size * $grid_size; ?>);

      // Get the system date
      var d = new Date();
      var hr = d.getHours();
      var mn = d.getMinutes();
      var sd = d.getSeconds();

      // The 18-digit binary time
      var t = six_digit_binary(hr) + six_digit_binary(mn) + six_digit_binary(sd);

      // Print each row as "pixels", then compute the next row, until the 18 by 18 grid is complete
      for(var j = 0; j < <?php echo $grid_size; ?>; j++)
      {
        print_row(j,t);
        t = successor_string(t);
      }

      // Print the hour digits
      digits_context.fillStyle = hour_color;
      digits_context.fillText(two_digit_decimal(hr),<?php echo ($pixel_size * $grid_size) / 6; ?>,<?php echo $pixel_size * $digits_height - $digit_bottom_margin; ?>);

      // Draw the line under the hour position
      digits_context.strokeStyle = hour_color;
      digits_context.beginPath();
      digits_context.moveTo(0,<?php echo $pixel_size * $digits_height - ($line_width / 2); ?>);
      digits_context.lineTo(<?php echo ($pixel_size * $grid_size) / 3; ?>,<?php echo $pixel_size * $digits_height - ($line_width / 2); ?>);
      digits_context.stroke();

      // Print the minute digits
      digits_context.fillStyle = minute_color;
      digits_context.fillText(two_digit_decimal(mn),<?php echo ($pixel_size * $grid_size * 3) / 6; ?>,<?php echo $pixel_size * $digits_height - $digit_bottom_margin; ?>);

      // Draw the line under the minute position
      digits_context.strokeStyle = minute_color;
      digits_context.beginPath();
      digits_context.moveTo(<?php echo ($pixel_size * $grid_size) / 3; ?>,<?php echo $pixel_size * $digits_height - ($line_width / 2); ?>);
      digits_context.lineTo(<?php echo ($pixel_size * $grid_size * 2) / 3; ?>,<?php echo $pixel_size * $digits_height - ($line_width / 2); ?>);
      digits_context.stroke();

      // Print the second digits
      digits_context.fillStyle = second_color;
      digits_context.fillText(two_digit_decimal(sd),<?php echo ($pixel_size * $grid_size * 5) / 6; ?>,<?php echo $pixel_size * $digits_height - $digit_bottom_margin; ?>);

      // Draw the line under the second position
      digits_context.strokeStyle = second_color;
      digits_context.beginPath();
      digits_context.moveTo(<?php echo ($pixel_size * $grid_size * 2) / 3; ?>,<?php echo $pixel_size * $digits_height - ($line_width / 2); ?>);
      digits_context.lineTo(<?php echo $pixel_size * $grid_size; ?>,<?php echo $pixel_size * $digits_height - ($line_width / 2); ?>);
      digits_context.stroke();
    }

    draw();
  </script>

</body>

</html>