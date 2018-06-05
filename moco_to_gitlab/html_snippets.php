<?php

function create_frame_manage_users()
{
?>
    <div class='frame frame_mangeUsers'>
    <h1>Alle Benutzer</h1> 
    <form action="<?php $_SERVER['PHP_SELF'] ?>" method="post">


        <div class="btnContainer btnContainer_manageUsers">
                    <input type="submit" class="btnSuperUser btnManage" name="back" value="Zurück" />
                    <input type="submit" class="btnSuperUser btnManage" name="createUser" value="Benutzer anlegen" />
                    <input type="submit" class="button btnManage" name="logout" value="Ausloggen" />
                </div>

        <table id="table_id" class="display">
        <thead>
            <tr>
            <th>Moco Token</th>
                <th>GitLab Token</th>
                <th>Vorname</th>
                <th>Nachname</th>
                <th>Benutzer bearbeiten</th>
            </tr>
        </thead>
        <tbody>
<?php
}