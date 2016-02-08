# WordParser
PHP class for translate docx text in to html
The server should be installed pandoc.
Example:
$html = new DocxToHtml($adres_file,$path_to_copy_img);
echo $html->getHtml();
