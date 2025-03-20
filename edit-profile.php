<?php
include 'includes/main.inc.php';
include 'includes/user.inc.php';
include 'includes/template.php';

// Check logged-in
check_loggedin($pdo);

// Retrieve additional account info from the database because we don't have them stored in sessions
$stmt = $pdo->prepare('SELECT * FROM accounts WHERE username = :username');
$stmt->bindParam(':username', $_SESSION['name']);
$stmt->execute();
$account = $stmt->fetch(PDO::FETCH_ASSOC);


template_header('Edit Profile');

?>
<div id="header">
    <div class="wrap_second">
        <div class="logo"></div>
        <div class="nav">
            <ul>
                <?= random_manga($pdo); ?>
                <li><a href="/">Home</a></li>
                <li><a href="/forums/">Forums</a></li>
                <li><a href="/tags/">Tags</a></li>
                <li><a href="/artists/">Artists</a></li>
                <li><a href="/characters/">Characters</a></li>
                <li><a href="/info/">Info</a></li>
                <div class="clear"></div>
            </ul>
        </div>
        <div class="right">
            <div class="search">
                <form action="/search/" method="GET">
                    <input type="text" name="q" id="q" value="" placeholder="Search by titles, tags, artists, or characters.">
                    <button class="sbtn" type="submit"><i class="fa fa-search"></i></button>
                </form>
            </div>
            <button type="button" class="navbar-toggle collapsed" id="nav_btn">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <div class="navbar_links" style="display: none;">
                <ul>
                    <?= random_manga($pdo); ?>
                    <li><a href="/">Home</a></li>
                    <li><a href="/forums/">Forums</a></li>
                    <li><a href="/tags/">Tags</a></li>
                    <li><a href="/artists/">Artists</a></li>
                    <li><a href="/characters/">Characters</a></li>
                    <li><a href="/info/">Info</a></li>
                    <?php if (!isset($_SESSION['loggedin'])) { ?>
                        <li><a href="/login/">Login</a></li>
                        <li><a href="/register/"></i>Register</a></li>
                    <? } else { ?>
                        <li><a href="/user/<?= $_SESSION['name'] ?>/favorites/">Favorites</a></li>
                        <li><a href="/user/<?= $_SESSION['name'] ?>/"><span class="username"><?= $_SESSION['name'] ?></span></a></li>
                        <?php if ($_SESSION['role'] == '1') : ?>
                            <li><a href="/admincp/">AdminCP</a></li>
                        <?php endif; ?>
                        <?php if ($_SESSION['role'] == '2') : ?>
                            <li><a href="/modcp/">ModCP</a></li>
                        <?php endif; ?>
                        <li><a href="/logout/">Logout</a></li>
                    <? } ?>
                </ul>
            </div>
            <button type="button" class="drop_btn" id="drop_btn"><i class="fa fa-arrow-down"></i></button>
            <div id="dropdown_menu" style="display: none;">
                <ul>
                    <?= random_manga($pdo); ?>
                    <li><a href="/">Home</a></li>
                    <li><a href="/forums/">Forums</a></li>
                    <li><a href="/tags/">Tags</a></li>
                    <li><a href="/artists/">Artists</a></li>
                    <li><a href="/characters/">Characters</a></li>
                    <li><a href="/info/">Info</a></li>
                </ul>
            </div>
            <div class="nav sec">
                <ul>
                    <?php if (!isset($_SESSION['loggedin'])) { ?>
                        <li><a href="/login/"><i class="fa fa-sign-in"></i>Login</a></li>
                        <li><a href="/register/"><i class="fa fa-user"></i>Register</a></li>
                    <? } else { ?>
                        <li><a href="/user/<?= $_SESSION['name'] ?>/favorites/"><i class="fa fa-heart"></i>Favorites</a></li>
                        <li <? if ($_GET['user'] == $_SESSION['name']) : ?> class="active" <? endif; ?>><a href="/user/<?= $_SESSION['name'] ?>/"><i class="fa fa-user"></i><span class="username"><?= $_SESSION['name'] ?></span></a></li>
                        <?php if ($_SESSION['role'] == '1') : ?>
                            <li><a href="/admincp/"><i class="fa fa-cog"></i>AdminCP</a></li>
                        <?php endif; ?>
                        <?php if ($_SESSION['role'] == '2') : ?>
                            <li><a href="/modcp/"><i class="fa fa-cog"></i>ModCP</a></li>
                        <?php endif; ?>
                        <li><a href="/logout/"><i class="fa fa-sign-out"></i>Logout</a></li>
                    <? } ?>
                    <div class="clear"></div>
                </ul>
            </div>
            <div class="clear"></div>
        </div>
        <div class="clear"></div>
    </div>
</div>
<div id="content">
    <div class="wrap">
        <div class="inner_content">
            <div class="profile_edit">
                <h3>Avatar</h3>
                <div>
                    <div class="avt_div">

                        <img id="avatar_image" src="/uploads/<?= $account['avatar'] ?>" />
                    </div>
                    <form id="upl_avt" action="/user/edit_profile.php?act=avatar" method="post" enctype="multipart/form-data">
                        <table>
                            <tr>
                                <td>
                                    <div class="input-group">
                                        <label class="input-group-btn">
                                            <input type="file" id="file" name="file" />
                                        </label>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td><input class="btn btn-edit" type="button" name="save_avatar" id="save_avatar" value="Upload" /></td>
                            </tr>
                        </table>
                    </form>
                    <div id="avatar_msg"></div>
                </div>

                <div class="profile_divider"></div>

                <h3>Info</h3>
                <div>

                    <table>
                        <tr>
                            <td>
                                <p>Username (Not Changeable)</p><input type="text" id="username" name="username" value="<?= $account['username'] ?>" disabled />
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <p>E-mail Address</p><input type="text" id="email_address" name="email_address" value="<?= $account['email'] ?>" placeholder="Your e-mail address" />
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <p>Bio</p><textarea maxlength="500" id="bio" name="bio" placeholder="Type here something about you..."><?= $account['about'] ?></textarea>
                            </td>
                        </tr>
                    </table>
                    <ul>
                        <li class="p_age">
                            <p>Age</p>
                            <select name="age" id="age">
                                <option value=18>18</option>
                                <option value=19>19</option>
                                <option value=20>20</option>
                                <option value=21>21</option>
                                <option value=22>22</option>
                                <option value=23>23</option>
                                <option value=24>24</option>
                                <option value=25>25</option>
                                <option value=26>26</option>
                                <option value=27>27</option>
                                <option value=28>28</option>
                                <option value=29>29</option>
                                <option value=30>30</option>
                                <option value=31>31</option>
                                <option value=32>32</option>
                                <option value=33>33</option>
                                <option value=34>34</option>
                                <option value=35>35</option>
                                <option value=36>36</option>
                                <option value=37>37</option>
                                <option value=38>38</option>
                                <option value=39>39</option>
                                <option value=40>40</option>
                                <option value=41>41</option>
                                <option value=42>42</option>
                                <option value=43>43</option>
                                <option value=44>44</option>
                                <option value=45>45</option>
                                <option value=46>46</option>
                                <option value=47>47</option>
                                <option value=48>48</option>
                                <option value=49>49</option>
                                <option value=50>50</option>
                                <option value=51>51</option>
                                <option value=52>52</option>
                                <option value=53>53</option>
                                <option value=54>54</option>
                                <option value=55>55</option>
                                <option value=56>56</option>
                                <option value=57>57</option>
                                <option value=58>58</option>
                                <option value=59>59</option>
                                <option value=60>60</option>
                                <option value=61>61</option>
                                <option value=62>62</option>
                                <option value=63>63</option>
                                <option value=64>64</option>
                                <option value=65>65</option>
                                <option value=66>66</option>
                                <option value=67>67</option>
                                <option value=68>68</option>
                                <option value=69>69</option>
                                <option value=70>70</option>
                                <option value=71>71</option>
                                <option value=72>72</option>
                                <option value=73>73</option>
                                <option value=74>74</option>
                                <option value=75>75</option>
                                <option value=76>76</option>
                                <option value=77>77</option>
                                <option value=78>78</option>
                                <option value=79>79</option>
                                <option value=80>80</option>
                                <option value=81>81</option>
                                <option value=82>82</option>
                                <option value=83>83</option>
                                <option value=84>84</option>
                                <option value=85>85</option>
                                <option value=86>86</option>
                                <option value=87>87</option>
                                <option value=88>88</option>
                                <option value=89>89</option>
                                <option value=90>90</option>
                                <option value=91>91</option>
                                <option value=92>92</option>
                                <option value=93>93</option>
                                <option value=94>94</option>
                                <option value=95>95</option>
                                <option value=96>96</option>
                                <option value=97>97</option>
                                <option value=98>98</option>
                                <option value=99>99</option>
                                <option value=100>100</option>
                            </select>
                        </li>
                        <li class="p_gender">
                            <p>Gender</p>
                            <select name="gender" id="gender">
                                <option selected value="N/A">N/A</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Trap">Trap</option>
                            </select>
                        </li>
                        <div class="save_check">
                            <input class="btn btn-edit" style="display:block;" type="button" name="save_info" id="save_info" value="Save Info" />
                        </div>
                        <div class="clear"></div>
                    </ul>
                    <div id="info_msg"></div>
                </div>
                <div class="profile_divider"></div>
                <h3>Password</h3>
                <div>
                    <table>
                        <tr>
                            <td><input type="password" id="old_password" name="old_password" value="" placeholder="Your current password" /></td>
                        </tr>
                        <tr>
                            <td><input type="password" id="password" name="password" value="" placeholder="Type new password" /></td>
                        </tr>
                        <tr>
                            <td><input type="password" id="cpassword" name="cpassword" value="" placeholder="Confirm new password" /></td>
                        </tr>
                        <tr>
                            <td><input class="btn btn-edit" type="button" name="save_password" id="save_password" value="Update Password" /></td>
                        </tr>
                    </table>
                    <div class="clear"></div>
                    <div id="pass_msg"></div>
                </div>
                <div class="profile_divider"></div>
                <h3>2 Factor Authentication</h3>
                <div>
                    <table>
                        <tr>
                            <? if ($account['2fcode'] == '-1') { ?>
                                <td><input class="btn btn-edit" type="button" name="e_2_factor" id="e_2_factor" value="Enable 2FA" /></td>
                            <? } else { ?>
                                <td><input class="btn btn-edit" type="button" name="d_2_factor" id="d_2_factor" value="Disable 2FA" /></td>
                            <? } ?>
                        </tr>
                    </table>
                    <div class="clear"></div>
                    <div id="msg_2fa"></div>
                </div>
                <div class="separator"></div>
            </div>
        </div>
    </div>
</div>
<?= template_footer() ?>
<script>
    document.querySelector('#gender').value = '<?= $account['gender'] ?>';
    document.querySelector('#age').value = '<?= $account['age'] ?>'
</script>