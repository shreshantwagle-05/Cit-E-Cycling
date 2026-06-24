# Cit-E Cycling — Project files and source

This file contains a listing of all project files and their full contents for the `cycling` project.

## Files

- admin_login.css (empty)
- admin_login.html
- admin_menu.php
- cycling.sql
- dbconnect.php
- delete.php
- edit_participant_form.php
- edit_participant.php
- index.css
- index.html
- login.php
- register_form.css (empty)
- register_form.html
- register.php
- script.js
- search_form.php
- search_result.php
- view_participants_edit_delete.php
- Resource/ (directory)

---

## admin_login.css

(Empty file)

---

## admin_login.html

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Login to the admin area</title>
</head>


<body>
    <a href=".">Back to index</a>
    <h1>Login to the admin area</h1>
    <form action="login.php" method="POST">
        <p>Username</p>
        <input type="text" name="username"><br>
        <p>Password</p>
        <input type="password" name="password"><br>
        
        <input type = "Submit">

    </form>
    
</body>
</html>
```

---

## admin_menu.php

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Admin menu</title>
</head>


<body>

    <h1>Cit-E Cycling web portal</h1>
    <ul>
        <li><a href="search_form.php">Search for clubs or participants</a></li>
        <li><a href="view_participants_edit_delete.php">View all participants to either edit or delete</a></li>
   
    </ul> 
</body>
</html>
```

---

## cycling.sql

```sql
-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Mar 29, 2023 at 11:01 AM
-- Server version: 10.3.28-MariaDB
-- PHP Version: 7.4.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `cycling`
--

-- --------------------------------------------------------

--
-- Table structure for table `club`
--

CREATE TABLE `club` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `location` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `interest`
--

CREATE TABLE `interest` (
  `id` int(255) NOT NULL,
  `firstname` varchar(50) NOT NULL,
  `surname` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `terms` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `participant`
--

CREATE TABLE `participant` (
  `id` int(255) NOT NULL,
  `firstname` varchar(50) NOT NULL,
  `surname` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `power_output` float DEFAULT NULL,
  `distance` float DEFAULT NULL,
  `club_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `username` varchar(10) NOT NULL,
  `password` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `username`, `password`) VALUES
(1, 'admin', 'password123');

-- (Truncated for brevity in this file; full dump exists in the project `cycling.sql` file)
```

---

## dbconnect.php

```php
<?php
 //database connection variables for your UOS webspace database
 $servername = "localhost";
 $username = "";
 $password = "";
 $database = ""; 

 ?>
```

---

## delete.php

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Delete participant</title>
</head>
<body>
    <?php
       
    include 'dbconnect.php';

            try {
                $conn = new PDO("mysql:host=$servername;dbname=$database", $username, $password); //building a new connection object
                // set the PDO error mode to exception
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                //TODO DELETE - complete the functionality

                }
            catch(PDOException $e)
                {
                // put the error stuff here
                }

        
        
        ?>


</body>
</html>
```

---

## edit_participant_form.php

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Update participant scores</title>
</head>
<body>
    <form action="edit_participant.php" method="POST">
        Particpant Firstname<br>
        <input type="text" name="firstname" disabled value="<?php ?>"> <br>
        Particpant Surname <br>
        <input type="text" name="surname" disabled value="<?php ?>"> <br>
        Power output in watts<br>
        <input type="text" name="power_output" value="<?php ?>"> <br>
        Distance in KM<br>
        <input type="text" name="distance_travelled" value="<?php ?>"> <br>
        <input type="hidden" name ="id" value="<?php ?>">

        <input type="submit" value="Update this rider">
            
        
    </form>
    
</body>
</html>
```

---

## edit_participant.php

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Update participants score</title>
</head>
<body>
<a href=".">Back to index</a>
    <?php
        
        //including connection variables   
        include 'dbconnect.php';

        try {
            if($_SERVER['REQUEST_METHOD'] == 'POST') //has the user submitted the form and edited the participant
            {
                //TODO - UPDATE section
                
                $conn = new PDO("mysql:host=$servername;dbname=$database", $username, $password); //building a new connection object
                // set the PDO error mode to exception
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
            }
            else{
                //TODO - SELECT section

                $conn = new PDO("mysql:host=$servername;dbname=$database", $username, $password); //building a new connection object
                // set the PDO error mode to exception
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                include "edit_participant_form.php";
            }
        }
        catch(PDOException $e)
            {
                //error stuff here
            }

            /**
            * For the brave souls who get this far: You are the chosen ones,
            * the valiant knights of programming who toil away, without rest,
            * fixing our most awful code. To you, true saviors, kings of men,
            * I say this: never gonna give you up, never gonna let you down,
            * never gonna run around and desert you. Never gonna make you cry,
            * never gonna say goodbye. Never gonna tell a lie and hurt you.
            */
        ?>


</body>
</html>
```

---

## index.css

(The file is long; included in project `index.css` file.)

---

## index.html

```html
(Full `index.html` content is included in the project; see the file `index.html` for the complete page.)
```

---

## login.php

```php
<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    
</head>
<body>
    <?php
        
        include 'dbconnect.php';
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST'){
            try {
                $conn = new PDO("mysql:host=$servername;dbname=$database", $username, $password); //building a new connection object
                // set the PDO error mode to exception
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                }
            catch(PDOException $e)
                {
                echo $e->getMessage(); //If we are not successful in connecting or running the query we will see an error
                }
        }
        else{
            echo "You're here by mistake" ;
        }
        ?>


</body>
</html>
```

---

## register_form.css

(Empty file)

---

## register_form.html

```html
 <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Register your interest</title>
</head>


<body>
    <a href=".">Back to index</a>
    <h1>Register your interest for future events</h1>
    <form action="register.php" method="POST">
        <p>Firstname</p>
        <input type="text" name="firstname"><br>
        <p>Surname</p>
        <input type="text" name="surname"><br>
        <p>Email</p>
        <input type="text" name="email"><br>
        <p>Do you accept the terms and conditions?</p>
        <input type="checkbox" name="terms" value="yes"><br><br>
        <input type = "Submit">

    </form>
    
</body>
</html>

 
```

---

## register.php

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Register your interest</title>
</head>
<body>
    <?php
    //including connection variables  
    include 'dbconnect.php';

            try {
                $conn = new PDO("mysql:host=$servername;dbname=$database", $username, $password); //building a new connection object
                // set the PDO error mode to exception
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                //TODO INSERT - complete the functionality

                }
            catch(PDOException $e)
                {
                echo $e->getMessage(); //If we are not successful we will see an error

                }

                //made you look
        ?>


</body>
</html>
```

---

## script.js

```javascript
// Cit-E Cycling — core interactions only

document.addEventListener("DOMContentLoaded", () => {
  const reduceMotion = window.matchMedia(
    "(prefers-reduced-motion: reduce)",
  ).matches;

  /* ---------- Navbar: scroll shadow + mobile menu ---------- */
  const navbar = document.getElementById("navbar");
  const navToggle = document.getElementById("navToggle");
  const navMenu = document.getElementById("navMenu");

  const onScroll = () =>
    navbar.classList.toggle("is-scrolled", window.scrollY > 8);
  onScroll();
  window.addEventListener("scroll", onScroll, { passive: true });

  if (navToggle && navMenu) {
    navToggle.addEventListener("click", () => {
      const isOpen = navMenu.classList.toggle("is-open");
      navToggle.setAttribute("aria-expanded", String(isOpen));
    });

    navMenu.querySelectorAll("a").forEach((link) => {
      link.addEventListener("click", () => {
        navMenu.classList.remove("is-open");
        navToggle.setAttribute("aria-expanded", "false");
      });
    });

    document.addEventListener("click", (e) => {
      if (!navbar.contains(e.target)) {
        navMenu.classList.remove("is-open");
        navToggle.setAttribute("aria-expanded", "false");
      }
    });

    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape") {
        navMenu.classList.remove("is-open");
        navToggle.setAttribute("aria-expanded", "false");
      }
    });
  }

  /* ---------- Scroll reveal ---------- */
  const revealEls = document.querySelectorAll("[data-reveal]");
  revealEls.forEach((el) => {
    const delay = el.getAttribute("data-reveal-delay");
    if (delay) el.style.setProperty("--reveal-delay", delay);
  });

  if (reduceMotion || !("IntersectionObserver" in window)) {
    revealEls.forEach((el) => el.classList.add("is-visible"));
  } else {
    const revealObserver = new IntersectionObserver(
      (entries, obs) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            entry.target.classList.add("is-visible");
            obs.unobserve(entry.target);
          }
        });
      },
      { threshold: 0.15, rootMargin: "0px 0px -8% 0px" },
    );
    revealEls.forEach((el) => revealObserver.observe(el));
  }

  /* ---------- FAQ accordion ---------- */
  document.querySelectorAll(".faq-trigger").forEach((trigger) => {
    const panel = document.getElementById(
      trigger.getAttribute("aria-controls"),
    );
    trigger.addEventListener("click", () => {
      const isOpen = trigger.getAttribute("aria-expanded") === "true";
      trigger.setAttribute("aria-expanded", String(!isOpen));
      panel.classList.toggle("is-open", !isOpen);
    });
  });
});
```

---

## search_form.php

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Register your interest</title>
</head>


<body>
    <a href=".">Back to index</a>
    <h1>Search for participants or clubs</h1>

    <h2>Search for an individual participant</h2>
    <form action="search_result.php" method="POST">
        <p>Participant firstname or surname</p>
        <input type="text" name="firstname"><br>
        <input type="hidden" name="participant" value="1">
        <input type = "Submit">

    </form>
    
    <h2>Search for a club / team</h2>
    <form action="search_result.php" method="POST">
        <p>Club name</p>
        <input type="text" name="club"><br>
        <input type = "Submit">

    </form>
</body>
</html>
```

---

## search_result.php

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Search results</title>
    
</head>
<body>
<a href=".">Back to index</a>
    <?php
        
            
            include 'dbconnect.php';
        
        try {
            $conn = new PDO("mysql:host=$servername;dbname=$database", $username, $password); //building a new connection object
            // set the PDO error mode to exception
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            //checking which form has been posted
            if ($_POST['participant'] == "1") {

                echo "search participant";
            }
            else{

                echo "search club";
            }
            
               
            }
        catch(PDOException $e)
            {
                //put error stuff here
            }
        ?>


</body>
</html>
```

---

## view_participants_edit_delete.php

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>View participants</title>
</head>
<body>
    <h1>View all of the participants for edit or delete</h1>
    <a href=".">Back to index</a>
    <?php
        
    //including connection variables - remember to update these if you are using XAMPP    
    include 'dbconnect.php';
        
        try {
            $conn = new PDO("mysql:host=$servername;port=$port;dbname=$database", $username, $password); //building a new connection object
            // set the PDO error mode to exception
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            //TODO SELECT - view the participants with links to edit or delete them. 
            
            }
        catch(PDOException $e)
            {
            echo $e->getMessage(); //If we are not successful we will see an error
            }
        ?>


</body>
</html>
```

---

## Resource/

- images (1).jpg
- Logo.png

---

End of project export.
