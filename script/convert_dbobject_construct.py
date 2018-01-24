import sys
import re

BEGIN = re.compile('\n *\$db = .*\n *\$table = ')
END = re.compile(';\n *parent::__construct.*\n')

def partition(file):
    text = file.read();
    ret = []

    parts = BEGIN.split(text, 1)
    ret.append(parts[0])

    ret += END.split(parts[1], 1)

    return ret


def parentConstruct(db):
    return '\n        parent::__construct(DB::getInstance(), ' + db + ', $id, $properties);\n'


if __name__ == '__main__':
    filename = sys.argv[1]
    with open(filename) as f:
        parts = partition(f)

    with open(filename, 'w') as f:
        f.write(parts[0] + parentConstruct(parts[1]) + parts[2])
