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
    <form method="POST">
        From <input name='from' type='email' />
        To <input name='to' type='email' />
        Bcc <input name='bcc' type='email' />
        Subject <input name='subject' />
        Body <textarea name='body'></textarea>
        <span></span><span><button type="submit">发送</button></span>
    </form>

<?php
};
