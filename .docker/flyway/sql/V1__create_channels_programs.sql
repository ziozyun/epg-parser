CREATE TABLE IF NOT EXISTS channels (
  id BIGSERIAL PRIMARY KEY,
  external_id TEXT NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS channel_translations (
  id BIGSERIAL PRIMARY KEY,
  channel_id BIGINT NOT NULL REFERENCES channels(id) ON DELETE CASCADE,
  lang VARCHAR(10) NOT NULL,
  name TEXT NOT NULL,
  UNIQUE (channel_id, lang)
);

CREATE TABLE IF NOT EXISTS programs (
  id BIGSERIAL PRIMARY KEY,
  external_id TEXT NOT NULL UNIQUE,
  channel_id BIGINT NOT NULL REFERENCES channels(id) ON DELETE CASCADE,
  start_at TIMESTAMPTZ NOT NULL,
  end_at TIMESTAMPTZ NOT NULL
);

CREATE TABLE IF NOT EXISTS program_translations (
  id BIGSERIAL PRIMARY KEY,
  program_id BIGINT NOT NULL REFERENCES programs(id) ON DELETE CASCADE,
  lang VARCHAR(10) NOT NULL,
  title TEXT NOT NULL,
  UNIQUE (program_id, lang)
);

CREATE INDEX IF NOT EXISTS idx_programs_channel_id ON programs(channel_id);
CREATE INDEX IF NOT EXISTS idx_programs_start_at ON programs(start_at);
CREATE INDEX IF NOT EXISTS idx_programs_end_at ON programs(end_at);
CREATE UNIQUE INDEX IF NOT EXISTS idx_programs_channel_start ON programs(channel_id, start_at);
