<html>
  <head>
    <title>Antweb API</title>
  </head>
  <body>
    <h2>Welcome to the Antweb API</h2>

    <h3>Functions</h3>

    <h4>1. Get specimens by taxonomy rank and name.</h4>

    <p>You can query the specimen by asking for a particular taxonomic rank and name of that rank.  So if you want all specimens by the genus rank with the name Colobostruma your query would look like this:</p>

    <p>http://www.antweb.org/api/?rank=<b>genus</b>&name=<b>colobostruma</b></p>

    <p>If you do not specify a particular name of the rank you want, the API will return a list of disticnt names for that rank.  So if you want a list of disctinct subfamilies in the antweb database your query would look like this:</p>

    <p>http://www.antweb.org/api/?rank=<b>subfamily</b></p>

    <p><b>NOTE:</b> Querying for name alone will yield no results.</p>

    <h4>2. Get particular specimen using its unique code.</h4>

    <p>Each specimen is given a unique id in the antweb database, if you have a specific specimen you would like to look up you can query for it like this:</p>

    <p>http://www.antweb.org/api/?code=<b>CODE</b></p>

    <p>This will return all of the meta data surrounding the specimen, as well as a list of URLs for any images of the specimen.</p>

    <h4>3. Get specimens by decimal coordinates.</h4>
    <p>You can pull a list of specimens by digital coordinates.  Your query would look like this:</p>
    <p>http://www.antweb.org/api/?coord=<b>latitude</b>,<b>longitude</b></p>
    <p>The latidude and longitude must be separated by a comma.</p>
    <p>You can also define a specific radius in miles that you'd like to cover:</p>
    <p>http://www.antweb.org/api/?coord=<b>latitude</b>,<b>longitude</b>&r=<b>radius</b></p>
    <p>The radius defaults at 25 miles.</p>


  </body>

</html>