import sys
import re

BEGIN = re.compile('\n *\$fields = \[\n')
END = re.compile('\n *\];\n')
COMMENT = re.compile('\n *//.*\n')

def partition(file):
    text = file.read();
    ret = []

    parts = BEGIN.split(text, 1)
    ret.append(parts[0])

    parts = END.split(parts[1], 1)
    ret.append(COMMENT.sub('\n', parts[0]))
    ret.append(parts[1])

    return ret


def fieldsToProperties(text):
    def getName(line):
        return '    public $' + line.split("'")[1] + ';'
    return '\n'.join([getName(i) for i in text.split('\n')]) + '\n'


if __name__ == '__main__':
    filename = sys.argv[1]
    with open(filename) as f:
        parts = partition(f)

    with open(filename, 'w') as f:
        f.write(parts[0].replace('\n{\n', '\n{\n' + fieldsToProperties(parts[1]) + '\n', 1))
        f.write('\n' + parts[2])