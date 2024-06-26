let totp_inputs = [];

for(let i = 1; i <= 6; i++) {
    // On met tous les inputs dans un tableau
    totp_inputs[i] = document.getElementById(`totp${i}`);

    /* ------- Focus l'input suivant quand on a mis un nombre dans l'input courant ------- */
    totp_inputs[i].addEventListener("input", (e) => {
        // Si il n'est pas de type ajout de texte, on quitte la fonction
        if(e.inputType !== "insertText")
            return;

        // Si il est de type ajout de texte, on ne prevent pas le comportement par défaut (ajouter du texte dans l'input)

        // On récupère le next input
        let next_input = totp_inputs[i+1];


        // Si il existe on le focus
        next_input && next_input.focus();
    });

    /* ------- Focus l'input précédent quand on efface un nombre dans un input vide ------- */
    totp_inputs[i].addEventListener("keydown", (e) => {

        // Si l'utilisateur presse la touche effacter
        if(e.key === "Backspace") {

            // Si l'input sur lequel l'utilisateur est est vide
            if(e.target.value === "") {
                let new_input = totp_inputs[i-1];

                // On focus le précédent si il existe
                if(new_input) {
                    new_input.focus();
                    // et on efface son contenu
                    new_input.value = '';
                }
            }
        }
    });
}

/* ------- Support du controle V/clique droit coller sur le premier input ------- */
totp_inputs[1].addEventListener("paste", (e) => {
    let clipboard = e.clipboardData || window.clipboardData;
    let clipboard_data = clipboard.getData('Text');

    // On limite la borne supérieure de notre for loop à 6
    let upper_bound = (clipboard_data.length <= 6) ? clipboard_data.length : 6;

    for(let i = 0; i < upper_bound; i++) {
        totp_inputs[i+1].value = clipboard_data[i];
    }

    totp_inputs[upper_bound].focus();
});