--#region EMAIL

CREATE DOMAIN EMAIL AS VARCHAR(254)
	CHECK (value ~ '^[a-zA-Z0-9.!#$%&''*+/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]'
		'{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$');

--#endregion

--#region WEBSITE

CREATE DOMAIN WEBSITE AS VARCHAR(2048)
	CHECK (value ~ '^(http|ftp|https):\/\/([\w_-]+(?:(?:\.[\w_-]+)+))'
		'([\w.,@?^=%&:\/~+#-]*[\w@?^=%&\/~+#-])$');

--#endregion

--#region TELEPHONE
	
CREATE DOMAIN TELEPHONE AS VARCHAR(10) 
	CHECK (value ~ '^[0-9]{9,10}$');

--#endregion
