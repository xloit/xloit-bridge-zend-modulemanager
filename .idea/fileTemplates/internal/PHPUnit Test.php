<?php
#parse("PHP File Header.php")

#if (${NAMESPACE})
namespace ${NAMESPACE};
#end

/**
 * Test class for {@link ${NAME}}
 * 
#parse("Author.php")
#parse("Since.php")
#parse("PHP Package.php")
 */
class ${NAME} extends#if(${NAMESPACE}) \PHPUnit_Framework_TestCase #else PHPUnit_Framework_TestCase #end{

}
