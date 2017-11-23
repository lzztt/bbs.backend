import sys
import re

OPEN = re.compile('\)\n {4,6}\{\n')

if __name__ == '__main__':
    filename = sys.argv[1]
    with open(filename) as f:
        text = OPEN.sub(")\n    {$GLOBALS['func'][] = __FILE__ . ':' . __FUNCTION__;\n", f.read())

    with open(filename, 'w') as f:
        f.write(text)
