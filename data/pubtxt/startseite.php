<div class="content" id="public"><h2>Willkommen bei ZeroDayEmpire</h2>

    <?php
    $s1 = 'checked="checked" ';
    $usrname = $pwd = $sv = '';
    if (isset($_COOKIE['zdeLoginData4']) && substr_count($_COOKIE['zdeLoginData4'], "|") == 2) {
        list($server, $usrnameVal, $pwdVal) = explode("|", $_COOKIE['zdeLoginData4']);
        $var = "s".$server;
        $$var = "checked=\"checked\" ";
        $usrname = "value=\"".htmlspecialchars($usrnameVal, ENT_QUOTES)."\" ";
        $pwd = "value=\"[xpwd]\" ";
        $sv = "checked=\"checked\" ";
    }
    echo $notif ?? '';
    ?>

    <div id="public-login"><h3>Log In</h3>
        <form action="login.php?a=login" method="post">
            <table>
                <tr>
                    <th>Nickname:</th>
                    <td><input name="nick" maxlength="20" <?= $usrname ?>/></td>
                </tr>
                <tr>
                    <th>Passwort:</th>
                    <td><input type="password" name="pwd" <?= $pwd ?>/><br/>
                        <label><input type="checkbox" name="save" value="yes" <?= $sv ?>/> Login speichern</label>
                    </td>
                </tr>
                <tr>
                    <td colspan="2"><input type="hidden" name="server" value="1"/><input type="submit" value="Login"/>
                    </td>
                </tr>
            </table>
        </form>
    </div>
</div>
