<?php

function () {
?>

    <style>
        form {
            max-width: min(100vw, 600px);
            margin: auto;
            display: grid;
            grid-template-columns: 70px auto;
            column-gap: 10px;
            row-gap: 8px;
            justify-items: stretch;
            align-items: center;
        }
    </style>
    <form method="POST" autocomplete="on">
        From
        <input required name='from' type='email' />
        To
        <input required name='to' type='email' />
        Bcc
        <select required name="bcc">
            <option value="ikki3355@gmail.com">ikki3355@gmail.com</option>
            <option value="mikalotus3355@gmail.com">mikalotus3355@gmail.com</option>
        </select>
        Subject
        <input required name='subject' />
        Body
        <textarea required name='body'></textarea>
        <span></span>
        <span><button type="submit">发送</button></span>
    </form>

<?php
};
