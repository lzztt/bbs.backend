import sys


def partition(file):
    lines = file.readlines()
    ret = [[], [], []]

    part = 0
    for line in lines:
        if part == 0:
            if line[:4] == 'use ':
                part += 1
        elif part == 1:
            if line[:4] != 'use ':
                part += 1
        ret[part].append(line)

    return ret


def diff(l1, l2):
    if len(l1) != len(l2):
        return True
    for i in range(len(l1)):
        if l1[i] != l2[i]:
            return True
    return False


if __name__ == '__main__':
    filename = sys.argv[1]
    with open(filename) as f:
        parts = partition(f)

    if parts[1]:
        ordered = list(set(parts[1]))
        ordered.sort()
        if diff(parts[1], ordered):
            with open(filename, 'w') as f:
                f.write(''.join(parts[0] + ordered + parts[2]))
