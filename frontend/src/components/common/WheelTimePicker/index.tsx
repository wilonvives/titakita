import {useEffect, useRef, useState} from "react";
import {Button, Group, Popover, Text, TextInput} from "@mantine/core";
import {t} from "@lingui/macro";
import classes from "./WheelTimePicker.module.scss";

const ITEM_H = 36;
const VISIBLE = 5;
const PAD = ITEM_H * Math.floor(VISIBLE / 2);

const pad2 = (n: number) => String(n).padStart(2, "0");

interface WheelColumnProps {
    values: number[];
    value: number;
    onChange: (v: number) => void;
}

const WheelColumn = ({values, value, onChange}: WheelColumnProps) => {
    const ref = useRef<HTMLDivElement>(null);
    const timer = useRef<ReturnType<typeof setTimeout> | null>(null);
    const programmatic = useRef(false);

    const scrollToIndex = (index: number, smooth: boolean) => {
        const el = ref.current;
        if (!el) {
            return;
        }
        programmatic.current = true;
        el.scrollTo({top: index * ITEM_H, behavior: smooth ? "smooth" : "auto"});
        window.setTimeout(() => {
            programmatic.current = false;
        }, smooth ? 350 : 60);
    };

    useEffect(() => {
        const index = Math.max(0, values.indexOf(value));
        window.setTimeout(() => scrollToIndex(index, false), 0);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    const handleScroll = () => {
        if (programmatic.current) {
            return;
        }
        if (timer.current) {
            clearTimeout(timer.current);
        }
        timer.current = setTimeout(() => {
            const el = ref.current;
            if (!el) {
                return;
            }
            const index = Math.max(0, Math.min(values.length - 1, Math.round(el.scrollTop / ITEM_H)));
            scrollToIndex(index, true);
            const v = values[index];
            if (v !== value) {
                onChange(v);
            }
        }, 120);
    };

    return (
        <div
            ref={ref}
            className={classes.wheel}
            style={{height: VISIBLE * ITEM_H}}
            onScroll={handleScroll}
        >
            <div style={{height: PAD}}/>
            {values.map((v) => (
                <div
                    key={v}
                    className={classes.item}
                    style={{height: ITEM_H, opacity: v === value ? 1 : 0.3, fontWeight: v === value ? 700 : 400}}
                    onClick={() => {
                        scrollToIndex(values.indexOf(v), true);
                        onChange(v);
                    }}
                >
                    {pad2(v)}
                </div>
            ))}
            <div style={{height: PAD}}/>
        </div>
    );
};

interface WheelTimePickerProps {
    label?: string;
    value: string;
    onChange: (value: string) => void;
}

const parseTime = (v: string): { h: number; m: number } => {
    const [h, m] = (v || "10:00").split(":").map((n) => parseInt(n, 10));
    return {h: isNaN(h) ? 10 : h, m: isNaN(m) ? 0 : m};
};

export const WheelTimePicker = ({label, value, onChange}: WheelTimePickerProps) => {
    const [opened, setOpened] = useState(false);
    const init = parseTime(value);
    const [hour, setHour] = useState(init.h);
    const [minute, setMinute] = useState(init.m);

    useEffect(() => {
        const p = parseTime(value);
        setHour(p.h);
        setMinute(p.m);
    }, [value]);

    const hours = Array.from({length: 24}, (_, i) => i);
    const minutes = Array.from({length: 60}, (_, i) => i);

    const apply = () => {
        onChange(`${pad2(hour)}:${pad2(minute)}`);
        setOpened(false);
    };

    const cancel = () => {
        const p = parseTime(value);
        setHour(p.h);
        setMinute(p.m);
        setOpened(false);
    };

    return (
        <div>
            {label && <Text fw={500} size="sm" mb={4}>{label}</Text>}
            <Popover
                opened={opened}
                onChange={setOpened}
                position="bottom-start"
                withArrow
                shadow="md"
                trapFocus={false}
            >
                <Popover.Target>
                    <TextInput
                        readOnly
                        value={`${pad2(hour)}:${pad2(minute)}`}
                        onClick={() => setOpened((o) => !o)}
                        styles={{input: {cursor: "pointer"}}}
                    />
                </Popover.Target>
                <Popover.Dropdown p="sm">
                    <Group justify="center" gap={8} mb={2}>
                        <Text size="xs" c="dimmed" w={70} ta="center">{t`Hour`}</Text>
                        <Text size="xs" c="dimmed" w={70} ta="center">{t`Minute`}</Text>
                    </Group>
                    <div className={classes.wheelsWrap} style={{height: VISIBLE * ITEM_H}}>
                        <div className={classes.selectionBand} style={{top: PAD, height: ITEM_H}}/>
                        <WheelColumn values={hours} value={hour} onChange={setHour}/>
                        <WheelColumn values={minutes} value={minute} onChange={setMinute}/>
                    </div>
                    <Group justify="space-between" mt="sm">
                        <Button variant="default" size="xs" onClick={cancel}>{t`Cancel`}</Button>
                        <Button size="xs" onClick={apply}>{t`Done`}</Button>
                    </Group>
                </Popover.Dropdown>
            </Popover>
        </div>
    );
};

export default WheelTimePicker;
