<?php
// Prompt user for input
fwrite(STDOUT, "Please enter some data: ");

// Read a line from STDIN
$input = trim(fgets(STDIN)); // Reads user input or piped data

// Check if input was provided
if ($input !== "") {
    // Write to STDOUT (success case)
    fwrite(STDOUT, "OK: Received '$input'\n");
    exit(0); // Exit with success status
} else {
    // Write to STDERR (error case)
    fwrite(STDERR, "Erreur: Aucun texte saisi\n");
    exit(1); // Exit with error status
}
?>